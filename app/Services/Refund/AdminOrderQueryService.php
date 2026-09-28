<?php

namespace App\Services\Refund;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Read-only order queries for the admin dashboard, kept separate from
 * AdminRefundQueryService since orders and refund requests are browsed and
 * filtered independently.
 */
class AdminOrderQueryService
{
    /**
     * @param  array{customer_email?: string, order_number?: string, status?: string}  $filters
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginatedList(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Order::query()
            ->with('customer:id,name,email')
            ->withCount('items')
            ->latest('created_at');

        if (! empty($filters['customer_email'])) {
            $email = $filters['customer_email'];
            $query->whereHas('customer', fn ($q) => $q->where('email', 'ilike', "%{$email}%"));
        }

        if (! empty($filters['order_number'])) {
            $query->where('order_number', 'ilike', '%'.$filters['order_number'].'%');
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function findWithRelations(string $orderId): ?Order
    {
        return Order::query()
            ->with(['customer', 'items'])
            ->find($orderId);
    }
}
