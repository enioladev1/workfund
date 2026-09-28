<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\ActorType;
use App\Enums\AuditAction;
use App\Enums\DecisionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RefundIndexRequest;
use App\Http\Requests\Admin\ResolveRefundRequest;
use App\Http\Resources\AuditLogResource;
use App\Http\Resources\RefundRequestDetailResource;
use App\Http\Resources\RefundRequestResource;
use App\Models\RefundRequest;
use App\Services\Refund\AdminRefundQueryService;
use App\Services\Refund\AuditLogService;
use App\Services\Refund\RefundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function __construct(
        private readonly AdminRefundQueryService $queryService,
        private readonly RefundService $refundService,
        private readonly AuditLogService $auditLog,
    ) {}

    public function index(RefundIndexRequest $request): JsonResponse
    {
        $refunds = $this->queryService->paginatedList($request->filters(), (int) ($request->integer('per_page') ?: 20));

        return response()->json([
            'success' => true,
            'data' => RefundRequestResource::collection($refunds->items()),
            'meta' => [
                'current_page' => $refunds->currentPage(),
                'last_page' => $refunds->lastPage(),
                'per_page' => $refunds->perPage(),
                'total' => $refunds->total(),
            ],
        ]);
    }

    public function show(Request $request, string $refund): JsonResponse
    {
        $refundRequest = $this->queryService->findWithRelations($refund);

        if ($refundRequest === null) {
            return response()->json(['success' => false, 'message' => 'Refund request not found.'], 404);
        }

        $this->auditLog->record(
            'refund_request',
            $refundRequest->id,
            AuditAction::AdminViewedRefund,
            ActorType::Staff,
            (string) $request->user()->id,
        );

        return response()->json([
            'success' => true,
            'data' => new RefundRequestDetailResource($refundRequest),
        ]);
    }

    public function audit(string $refund): JsonResponse
    {
        $auditTrail = $this->queryService->auditTrail($refund);

        return response()->json([
            'success' => true,
            'data' => AuditLogResource::collection($auditTrail),
        ]);
    }

    public function resolve(ResolveRefundRequest $request, string $refund): JsonResponse
    {
        $refundRequest = RefundRequest::query()->findOrFail($refund);

        $updated = $this->refundService->resolveManually(
            $refundRequest,
            DecisionType::from($request->string('decision')->toString()),
            $request->string('reasoning')->toString(),
            $request->user(),
        );

        return response()->json([
            'success' => true,
            'data' => new RefundRequestResource($updated),
        ]);
    }
}
