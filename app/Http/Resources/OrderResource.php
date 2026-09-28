<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'currency' => $this->currency,
            'total' => Money::toDecimalString($this->total_minor),
            'is_final_sale' => $this->is_final_sale,
            'ordered_at' => $this->ordered_at->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'items' => $this->whenLoaded('items', fn () => OrderItemResource::collection($this->items)->resolve()),
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
            ]),
            'items_count' => $this->when(isset($this->items_count), fn () => $this->items_count),
        ];
    }
}
