<?php

namespace App\Http\Resources;

use App\Models\RefundRequest;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RefundRequest
 */
class RefundRequestDetailResource extends JsonResource
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
            'customer_message' => $this->customer_message,
            'requested_amount' => Money::toDecimalString($this->requested_amount_minor),
            'currency' => $this->currency,
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
            ]),
            'order' => $this->whenLoaded('order', fn () => (new OrderResource($this->order))->resolve()),
            'order_item' => $this->orderItem ? (new OrderItemResource($this->orderItem))->resolve() : null,
            'decisions' => $this->whenLoaded('decisions', fn () => RefundDecisionResource::collection($this->decisions)->resolve()),
            'ai_interactions' => $this->whenLoaded('aiInteractions', fn () => AiInteractionResource::collection($this->aiInteractions)->resolve()),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
