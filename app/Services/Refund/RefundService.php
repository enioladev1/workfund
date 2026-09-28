<?php

namespace App\Services\Refund;

use App\Enums\ActorType;
use App\Enums\AuditAction;
use App\Enums\DecidedBy;
use App\Enums\DecisionType;
use App\Enums\RefundStatus;
use App\Exceptions\SafeApiException;
use App\Mail\RefundDecisionMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RefundDecision;
use App\Models\RefundRequest;
use App\Models\User;
use App\Services\AI\AiService;
use App\Services\AI\DTOs\RefundAnalysisContext;
use App\Services\Refund\DTOs\RefundSubmissionResult;
use App\Services\Refund\DTOs\SubmitRefundData;
use App\Support\Money;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Orchestrates the full refund workflow:
 * validation -> retrieval -> policy -> AI -> decision -> audit -> response.
 * See RefundController for the thin controller that calls this.
 */
class RefundService
{
    public function __construct(
        private readonly RefundPolicyService $policyService,
        private readonly RefundDecisionService $decisionService,
        private readonly AiService $aiService,
        private readonly AuditLogService $auditLog,
        private readonly CustomerMessageBuilder $messageBuilder,
        private readonly OrderLookupService $orderLookup,
    ) {}

    public function submit(SubmitRefundData $data): RefundSubmissionResult
    {
        if ($data->idempotencyKey !== null) {
            $existing = RefundRequest::query()->where('idempotency_key', $data->idempotencyKey)->first();

            if ($existing !== null) {
                $this->auditLog->record('refund_request', $existing->id, AuditAction::IdempotentReplay);

                return new RefundSubmissionResult(
                    refundRequest: $existing,
                    customerMessage: $this->messageForExisting($existing),
                    wasIdempotentReplay: true,
                );
            }
        }

        $order = $this->orderLookup->findForCustomer($data->email, $data->orderNumber);
        $customer = $order->customer;

        $orderItem = null;

        if ($data->orderItemId !== null) {
            $orderItem = OrderItem::query()
                ->where('id', $data->orderItemId)
                ->where('order_id', $order->id)
                ->first();

            if ($orderItem === null) {
                throw new SafeApiException("We couldn't find that item on the order. Please check and try again.", 404);
            }
        }

        try {
            [$refundRequest, $policyResult] = DB::transaction(function () use ($customer, $order, $orderItem, $data) {
                /** @var Order $lockedOrder */
                $lockedOrder = Order::query()->where('id', $order->id)->lockForUpdate()->first();

                $requestedAmountMinor = $this->resolveRequestedAmount($lockedOrder, $orderItem, $data->requestedAmountMinor);

                $policyResult = $this->policyService->evaluate($lockedOrder, $orderItem, $requestedAmountMinor);

                $refundRequest = RefundRequest::create([
                    'customer_id' => $customer->id,
                    'order_id' => $lockedOrder->id,
                    'order_item_id' => $orderItem?->id,
                    'requested_amount_minor' => $requestedAmountMinor,
                    'currency' => $lockedOrder->currency,
                    'reason' => $data->reason,
                    'customer_message' => $data->customerMessage,
                    'status' => RefundStatus::Pending,
                    'idempotency_key' => $data->idempotencyKey,
                ]);

                $this->auditLog->record('refund_request', $refundRequest->id, AuditAction::RefundRequested, ActorType::Customer, $customer->id);
                $this->auditLog->record('refund_request', $refundRequest->id, AuditAction::OrderRetrieved, ActorType::System, metadata: ['order_id' => $lockedOrder->id]);
                $this->auditLog->record('refund_request', $refundRequest->id, AuditAction::PolicyEvaluated, ActorType::System, metadata: $policyResult->toArray());

                return [$refundRequest, $policyResult];
            });
        } catch (UniqueConstraintViolationException) {
            $existing = RefundRequest::query()->where('idempotency_key', $data->idempotencyKey)->first();

            if ($existing !== null) {
                $this->auditLog->record('refund_request', $existing->id, AuditAction::IdempotentReplay);

                return new RefundSubmissionResult($existing, $this->messageForExisting($existing), true);
            }

            throw new SafeApiException('Something went wrong while processing your refund request. Please try again.', 500);
        }

        $context = $this->buildAnalysisContext($order, $orderItem, $refundRequest, $data);
        $aiOutcome = $this->aiService->analyze($refundRequest, $context);

        $this->auditLog->record('refund_request', $refundRequest->id, AuditAction::AiAnalyzed, ActorType::System, metadata: [
            'succeeded' => $aiOutcome['analysis'] !== null,
            'injection_detected' => $aiOutcome['injection_detected'],
        ]);

        $decisionResult = $this->decisionService->decide($policyResult, $aiOutcome['analysis'], $aiOutcome['injection_detected']);

        DB::transaction(function () use ($refundRequest, $decisionResult) {
            /** @var RefundRequest $lockedRequest */
            $lockedRequest = RefundRequest::query()->where('id', $refundRequest->id)->lockForUpdate()->first();

            RefundDecision::create([
                'refund_request_id' => $lockedRequest->id,
                'decision' => $decisionResult->decision,
                'policy_result' => $decisionResult->policyResult->toArray(),
                'ai_result' => $decisionResult->aiAnalysis?->toArray(),
                'reasoning' => $decisionResult->internalReasoning,
            ]);

            $lockedRequest->update(['status' => $decisionResult->decision->toRefundStatus()]);

            $this->auditLog->record('refund_request', $lockedRequest->id, $this->decisionAuditAction($decisionResult->decision));
        });

        $refundRequest->refresh();

        $customerMessage = $this->messageBuilder->build($decisionResult);

        $this->notifyCustomer($refundRequest, $customerMessage);

        return new RefundSubmissionResult(
            refundRequest: $refundRequest,
            customerMessage: $customerMessage,
            wasIdempotentReplay: false,
        );
    }

    /**
     * Never lets a mail/queue problem break the refund flow: the decision is
     * already committed, so a notification failure is logged and swallowed.
     */
    private function notifyCustomer(RefundRequest $refundRequest, string $message): void
    {
        try {
            Mail::to($refundRequest->customer->email)->queue(new RefundDecisionMail($refundRequest, $message));
        } catch (Throwable $e) {
            Log::warning('Failed to queue refund decision notification email.', [
                'refund_request_id' => $refundRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * A staff member resolves an escalated request. Only escalated requests can
     * be resolved this way; anything else must go through the automated pipeline.
     */
    public function resolveManually(RefundRequest $refundRequest, DecisionType $decision, string $reasoning, User $staff): RefundRequest
    {
        if ($decision === DecisionType::Escalated) {
            throw new SafeApiException('An escalated request must be resolved as approved or denied.', 422);
        }

        $resolved = DB::transaction(function () use ($refundRequest, $decision, $reasoning, $staff) {
            /** @var RefundRequest $lockedRequest */
            $lockedRequest = RefundRequest::query()->where('id', $refundRequest->id)->lockForUpdate()->first();

            if ($lockedRequest->status !== RefundStatus::Escalated) {
                throw new SafeApiException('Only escalated requests can be manually resolved.', 422);
            }

            $latestDecision = $lockedRequest->decisions()->latest('created_at')->first();

            RefundDecision::create([
                'refund_request_id' => $lockedRequest->id,
                'decision' => $decision,
                'policy_result' => $latestDecision->policy_result ?? ['rules' => []],
                'ai_result' => $latestDecision?->ai_result,
                'reasoning' => $reasoning,
                'decided_by' => DecidedBy::Staff,
                'decided_by_user_id' => $staff->id,
            ]);

            $lockedRequest->update(['status' => $decision->toRefundStatus()]);

            $this->auditLog->record(
                'refund_request',
                $lockedRequest->id,
                AuditAction::ManuallyResolved,
                ActorType::Staff,
                (string) $staff->id,
                ['decision' => $decision->value],
            );

            return $lockedRequest->refresh();
        });

        $this->notifyCustomer($resolved, $this->messageForManualDecision($decision));

        return $resolved;
    }

    private function messageForManualDecision(DecisionType $decision): string
    {
        return match ($decision) {
            DecisionType::Approved => 'Your refund request was approved after manual review by our support team.',
            DecisionType::Denied => 'After manual review, your refund request was not approved under our current refund policy.',
            DecisionType::Escalated => 'Your request has been sent for review.',
        };
    }

    /**
     * Only checks the amount is sane relative to what the item/order is actually
     * worth. Whether that amount has already been refunded is the policy
     * engine's job (the "not_already_refunded" rule), not this method's.
     */
    private function resolveRequestedAmount(Order $order, ?OrderItem $orderItem, ?int $requestedAmountMinor): int
    {
        $faceValue = $orderItem->total_price_minor ?? $order->total_minor;
        $amount = $requestedAmountMinor ?? $faceValue;

        if ($amount <= 0 || $amount > $faceValue) {
            throw new SafeApiException('The refund amount requested is not valid for this order.', 422);
        }

        return $amount;
    }

    private function buildAnalysisContext(Order $order, ?OrderItem $orderItem, RefundRequest $refundRequest, SubmitRefundData $data): RefundAnalysisContext
    {
        $referenceDate = $order->delivered_at ?? $order->ordered_at;

        return new RefundAnalysisContext(
            orderNumber: $order->order_number,
            orderStatus: $order->status->value,
            isFinalSale: $orderItem->is_final_sale ?? $order->is_final_sale,
            orderAgeDays: (int) $referenceDate->diffInDays(now()),
            itemName: $orderItem->name ?? 'Whole order',
            requestedReason: $data->reason->label(),
            requestedAmountFormatted: Money::format($refundRequest->requested_amount_minor, $order->currency),
            orderTotalFormatted: Money::format($order->total_minor, $order->currency),
            customerMessage: $data->customerMessage,
        );
    }

    private function messageForExisting(RefundRequest $refundRequest): string
    {
        return match ($refundRequest->status) {
            RefundStatus::Approved => 'Your refund request was already approved.',
            RefundStatus::Denied => 'Your refund request was already reviewed and denied under our current refund policy.',
            RefundStatus::Escalated => 'Your request has already been sent for review. A support specialist will follow up.',
            RefundStatus::Pending => 'Your refund request is still being processed.',
        };
    }

    private function decisionAuditAction(DecisionType $decision): AuditAction
    {
        return match ($decision) {
            DecisionType::Approved => AuditAction::Approved,
            DecisionType::Denied => AuditAction::Denied,
            DecisionType::Escalated => AuditAction::Escalated,
        };
    }
}
