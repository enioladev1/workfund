<?php

namespace App\Models;

use App\Enums\RefundReason;
use App\Enums\RefundStatus;
use Database\Factories\RefundRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $customer_id
 * @property string $order_id
 * @property string|null $order_item_id
 * @property int $requested_amount_minor
 * @property string $currency
 * @property RefundReason $reason
 * @property string $customer_message
 * @property RefundStatus $status
 * @property string|null $idempotency_key
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['customer_id', 'order_id', 'order_item_id', 'requested_amount_minor', 'currency', 'reason', 'customer_message', 'status', 'idempotency_key'])]
class RefundRequest extends Model
{
    /** @use HasFactory<RefundRequestFactory> */
    use HasFactory, HasUuids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reason' => RefundReason::class,
            'status' => RefundStatus::class,
            'requested_amount_minor' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<OrderItem, $this>
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * @return HasMany<RefundDecision, $this>
     */
    public function decisions(): HasMany
    {
        return $this->hasMany(RefundDecision::class);
    }

    /**
     * @return HasMany<AiInteraction, $this>
     */
    public function aiInteractions(): HasMany
    {
        return $this->hasMany(AiInteraction::class);
    }

    public function latestDecision(): ?RefundDecision
    {
        return $this->decisions()->latest('created_at')->first();
    }
}
