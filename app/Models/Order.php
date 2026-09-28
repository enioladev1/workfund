<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
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
 * @property string $order_number
 * @property OrderStatus $status
 * @property string $currency
 * @property int $total_minor
 * @property bool $is_final_sale
 * @property Carbon $ordered_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['customer_id', 'order_number', 'status', 'currency', 'total_minor', 'is_final_sale', 'ordered_at', 'delivered_at'])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, HasUuids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'is_final_sale' => 'boolean',
            'total_minor' => 'integer',
            'ordered_at' => 'datetime',
            'delivered_at' => 'datetime',
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
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<RefundRequest, $this>
     */
    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class);
    }

    /**
     * Total minor units already refunded across approved requests for this order.
     */
    public function refundedAmountMinor(): int
    {
        return (int) $this->refundRequests()
            ->where('status', 'approved')
            ->sum('requested_amount_minor');
    }
}
