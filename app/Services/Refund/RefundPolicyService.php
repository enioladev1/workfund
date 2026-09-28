<?php

namespace App\Services\Refund;

use App\Enums\DecisionType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RefundPolicyRule;
use App\Support\PolicyEvaluationResult;
use App\Support\PolicyRuleResult;
use Illuminate\Support\Facades\Cache;

/**
 * Deterministic, authoritative refund policy. Nothing here can be overridden by
 * the AI: this class alone decides hard denials/escalations. AI only weighs in
 * on requests that pass every hard rule.
 */
class RefundPolicyService
{
    private const CACHE_KEY = 'refund_policy:rules';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return Cache::remember(self::CACHE_KEY, config('refund_policy.cache_ttl_seconds'), function () {
            $rows = RefundPolicyRule::query()->get(['key', 'value']);

            if ($rows->isEmpty()) {
                return $this->defaults();
            }

            return $rows->mapWithKeys(fn (RefundPolicyRule $rule) => [$rule->key => $rule->value['value'] ?? $rule->value])->all()
                + $this->defaults();
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return [
            'refund_window_days' => config('refund_policy.refund_window_days'),
            'human_review_threshold_minor' => config('refund_policy.human_review_threshold_minor'),
            'final_sale_blocks_refund' => config('refund_policy.final_sale_blocks_refund'),
        ];
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function evaluate(Order $order, ?OrderItem $orderItem, int $requestedAmountMinor): PolicyEvaluationResult
    {
        $rules = $this->rules();

        $ruleResults = [
            $this->evaluateNotAlreadyRefunded($order, $requestedAmountMinor),
            $this->evaluateFinalSale($order, $orderItem, (bool) $rules['final_sale_blocks_refund']),
            $this->evaluateRefundWindow($order, (int) $rules['refund_window_days']),
            $this->evaluateThreshold($requestedAmountMinor, (int) $rules['human_review_threshold_minor']),
        ];

        [$hardDecision, $reason] = $this->deriveHardDecision($ruleResults);

        return new PolicyEvaluationResult($ruleResults, $hardDecision, $reason);
    }

    private function evaluateNotAlreadyRefunded(Order $order, int $requestedAmountMinor): PolicyRuleResult
    {
        $alreadyRefunded = $order->refundedAmountMinor();
        $remaining = $order->total_minor - $alreadyRefunded;
        $passed = $requestedAmountMinor <= $remaining;

        return new PolicyRuleResult(
            key: 'not_already_refunded',
            label: 'Already refunded',
            passed: $passed,
            message: $passed
                ? 'This order has remaining refundable balance.'
                : 'This order has already been refunded up to its total value.',
        );
    }

    private function evaluateFinalSale(Order $order, ?OrderItem $orderItem, bool $finalSaleBlocksRefund): PolicyRuleResult
    {
        $isFinalSale = $orderItem->is_final_sale ?? $order->is_final_sale;
        $passed = ! ($finalSaleBlocksRefund && $isFinalSale);

        return new PolicyRuleResult(
            key: 'final_sale',
            label: 'Final sale',
            passed: $passed,
            message: $passed
                ? 'Item is not marked as final sale.'
                : 'This item is marked as final sale and is not eligible for a refund.',
        );
    }

    private function evaluateRefundWindow(Order $order, int $windowDays): PolicyRuleResult
    {
        $referenceDate = $order->delivered_at ?? $order->ordered_at;
        $ageInDays = (int) $referenceDate->diffInDays(now());
        $passed = $ageInDays <= $windowDays;

        return new PolicyRuleResult(
            key: 'refund_window',
            label: 'Refund window',
            passed: $passed,
            message: $passed
                ? "Order is {$ageInDays} days old, within the {$windowDays}-day refund window."
                : "Order is {$ageInDays} days old, which is beyond the {$windowDays}-day refund window.",
        );
    }

    private function evaluateThreshold(int $requestedAmountMinor, int $thresholdMinor): PolicyRuleResult
    {
        $passed = $requestedAmountMinor <= $thresholdMinor;

        return new PolicyRuleResult(
            key: 'refund_threshold',
            label: 'Refund threshold',
            passed: $passed,
            message: $passed
                ? 'Refund amount is within the automatic-approval threshold.'
                : 'Refund amount exceeds the threshold that requires human review.',
        );
    }

    /**
     * @param  list<PolicyRuleResult>  $ruleResults
     * @return array{0: ?DecisionType, 1: ?string}
     */
    private function deriveHardDecision(array $ruleResults): array
    {
        $byKey = [];
        foreach ($ruleResults as $rule) {
            $byKey[$rule->key] = $rule;
        }

        if (! $byKey['not_already_refunded']->passed) {
            return [DecisionType::Denied, $byKey['not_already_refunded']->message];
        }

        if (! $byKey['final_sale']->passed) {
            return [DecisionType::Denied, $byKey['final_sale']->message];
        }

        if (! $byKey['refund_window']->passed) {
            return [DecisionType::Denied, $byKey['refund_window']->message];
        }

        if (! $byKey['refund_threshold']->passed) {
            return [DecisionType::Escalated, $byKey['refund_threshold']->message];
        }

        return [null, null];
    }
}
