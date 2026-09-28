<?php

namespace App\Http\Resources;

use App\Models\RefundRequest;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RefundRequest
 */
class RefundRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'reason' => $this->reason->value,
            'requested_amount' => Money::toDecimalString($this->requested_amount_minor),
            'currency' => $this->currency,
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
            ]),
            'order' => [
                'order_number' => $this->whenLoaded('order', fn () => $this->order->order_number),
            ],
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
