<?php

namespace App\Http\Resources;

use App\Models\OrderItem;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderItem
 */
class OrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'unit_price' => Money::toDecimalString($this->unit_price_minor),
            'total_price' => Money::toDecimalString($this->total_price_minor),
            'is_final_sale' => $this->is_final_sale,
        ];
    }
}
