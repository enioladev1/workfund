<?php

namespace App\Services\Refund;

use App\Enums\RefundStatus;
use App\Models\AuditLog;
use App\Models\RefundRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Read-only queries backing both the admin JSON API and the Inertia admin
 * pages, so both consumers share one implementation instead of duplicating
 * filtering/pagination/eager-loading logic.
 */
class AdminRefundQueryService
{
    /**
     * @param  array{status?: string, reason?: string, customer_email?: string, order_number?: string, date_from?: string, date_to?: string}  $filters
     * @return LengthAwarePaginator<int, RefundRequest>
     */
    public function paginatedList(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = RefundRequest::query()
            ->with(['customer:id,name,email', 'order:id,order_number,total_minor,currency'])
            ->withCount('decisions')
            ->latest('created_at');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['reason'])) {
            $query->where('reason', $filters['reason']);
        }

        if (! empty($filters['customer_email'])) {
            $email = $filters['customer_email'];
            $query->whereHas('customer', fn ($q) => $q->where('email', 'ilike', "%{$email}%"));
        }

        if (! empty($filters['order_number'])) {
            $orderNumber = $filters['order_number'];
            $query->whereHas('order', fn ($q) => $q->where('order_number', 'ilike', "%{$orderNumber}%"));
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function findWithRelations(string $refundRequestId): ?RefundRequest
    {
        return RefundRequest::query()
            ->with([
                'customer',
                'order',
                'orderItem',
                'decisions' => fn ($q) => $q->latest('created_at'),
                'decisions.decidedByUser:id,name,email',
                'aiInteractions' => fn ($q) => $q->latest('created_at'),
            ])
            ->find($refundRequestId);
    }

    /**
     * @return Collection<int, AuditLog>
     */
    public function auditTrail(string $refundRequestId): Collection
    {
        return AuditLog::query()
            ->where('entity_type', 'refund_request')
            ->where('entity_id', $refundRequestId)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @return array<string, int>
     */
    public function dashboardStats(): array
    {
        $counts = RefundRequest::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'total' => (int) $counts->sum(),
            'approved' => (int) ($counts[RefundStatus::Approved->value] ?? 0),
            'denied' => (int) ($counts[RefundStatus::Denied->value] ?? 0),
            'escalated' => (int) ($counts[RefundStatus::Escalated->value] ?? 0),
            'pending' => (int) ($counts[RefundStatus::Pending->value] ?? 0),
        ];
    }

    /**
     * @return Collection<int, RefundRequest>
     */
    public function recent(int $limit = 10): Collection
    {
        return RefundRequest::query()
            ->with(['customer:id,name,email', 'order:id,order_number'])
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
