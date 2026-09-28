<?php

namespace App\Http\Controllers\Api\Customer;

use App\Enums\RefundReason;
use App\Exceptions\SafeApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Refund\ShowRefundStatusRequest;
use App\Http\Requests\Refund\StoreRefundSubmissionRequest;
use App\Http\Resources\Customer\RefundStatusResource;
use App\Models\RefundRequest;
use App\Services\Refund\DTOs\SubmitRefundData;
use App\Services\Refund\RefundService;
use Illuminate\Http\JsonResponse;

class RefundController extends Controller
{
    public function __construct(private readonly RefundService $refundService) {}

    public function store(StoreRefundSubmissionRequest $request): JsonResponse
    {
        $data = new SubmitRefundData(
            email: $request->string('email')->toString(),
            orderNumber: $request->string('order_number')->toString(),
            orderItemId: $request->string('order_item_id')->toString() ?: null,
            reason: RefundReason::from($request->string('reason')->toString()),
            customerMessage: $request->string('customer_message')->toString(),
            requestedAmountMinor: $request->integer('requested_amount_minor') ?: null,
            idempotencyKey: $request->header('Idempotency-Key') ?: null,
        );

        $result = $this->refundService->submit($data);

        return response()->json([
            'success' => true,
            'data' => new RefundStatusResource($result->refundRequest),
        ], $result->wasIdempotentReplay ? 200 : 201);
    }

    /**
     * Customers must prove ownership with their email; a UUID alone is never
     * enough to view a refund's status (IDOR protection).
     */
    public function show(ShowRefundStatusRequest $request, string $refund): JsonResponse
    {
        $refundRequest = RefundRequest::query()->with('customer')->find($refund);

        $providedEmail = mb_strtolower($request->string('email')->toString());

        if ($refundRequest === null || mb_strtolower($refundRequest->customer->email) !== $providedEmail) {
            throw new SafeApiException("We couldn't find a refund request matching those details.", 404);
        }

        return response()->json([
            'success' => true,
            'data' => new RefundStatusResource($refundRequest),
        ]);
    }
}
