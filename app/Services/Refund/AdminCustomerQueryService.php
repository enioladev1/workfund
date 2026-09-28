<?php

namespace App\Services\Refund;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AdminCustomerQueryService
{
    /**
     * @return Collection<int, Customer>
     */
    public function search(string $query, int $limit = 10): Collection
    {
        return Customer::query()
            ->where(fn ($q) => $q->where('name', 'ilike', "%{$query}%")->orWhere('email', 'ilike', "%{$query}%"))
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'email']);
    }

    /**
     * @param  array{email?: string, name?: string}  $filters
     * @return LengthAwarePaginator<int, Customer>
     */
    public function paginatedList(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Customer::query()
            ->withCount(['orders', 'refundRequests'])
            ->latest('created_at');

        if (! empty($filters['email'])) {
            $query->where('email', 'ilike', '%'.$filters['email'].'%');
        }

        if (! empty($filters['name'])) {
            $query->where('name', 'ilike', '%'.$filters['name'].'%');
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
