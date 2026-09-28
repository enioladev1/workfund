<?php

namespace App\Http\Resources;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Customer
 */
class AdminCustomerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'orders_count' => $this->when(isset($this->orders_count), fn () => $this->orders_count),
            'refund_requests_count' => $this->when(isset($this->refund_requests_count), fn () => $this->refund_requests_count),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
