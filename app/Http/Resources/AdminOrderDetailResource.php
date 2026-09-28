<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class AdminOrderDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $refundedMinor = $this->refundedAmountMinor();

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'currency' => $this->currency,
            'total' => Money::toDecimalString($this->total_minor),
            'refunded_amount' => Money::toDecimalString($refundedMinor),
            'remaining_refundable' => Money::toDecimalString($this->total_minor - $refundedMinor),
            'is_final_sale' => $this->is_final_sale,
            'ordered_at' => $this->ordered_at->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
            ]),
            'items' => $this->whenLoaded('items', fn () => OrderItemResource::collection($this->items)->resolve()),
        ];
    }
}
