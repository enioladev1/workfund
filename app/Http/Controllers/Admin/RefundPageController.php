<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ActorType;
use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RefundIndexRequest;
use App\Http\Resources\AuditLogResource;
use App\Http\Resources\RefundRequestDetailResource;
use App\Http\Resources\RefundRequestResource;
use App\Services\Refund\AdminRefundQueryService;
use App\Services\Refund\AuditLogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RefundPageController extends Controller
{
    public function __construct(
        private readonly AdminRefundQueryService $queryService,
        private readonly AuditLogService $auditLog,
    ) {}

    public function index(RefundIndexRequest $request): Response
    {
        $refunds = $this->queryService->paginatedList($request->filters());

        return Inertia::render('admin/refunds/index', [
            'refunds' => RefundRequestResource::collection($refunds->items())->resolve(),
            'meta' => [
                'current_page' => $refunds->currentPage(),
                'last_page' => $refunds->lastPage(),
                'total' => $refunds->total(),
            ],
            'filters' => $request->filters(),
        ]);
    }

    public function show(Request $request, string $refund): Response
    {
        $refundRequest = $this->queryService->findWithRelations($refund);

        abort_if($refundRequest === null, 404);

        $this->auditLog->record(
            'refund_request',
            $refundRequest->id,
            AuditAction::AdminViewedRefund,
            ActorType::Staff,
            (string) $request->user()->id,
        );

        return Inertia::render('admin/refunds/show', [
            'refund' => (new RefundRequestDetailResource($refundRequest))->resolve(),
            'audit' => AuditLogResource::collection($this->queryService->auditTrail($refund))->resolve(),
        ]);
    }
}
