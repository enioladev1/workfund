<?php

namespace Database\Factories;

use App\Enums\RefundReason;
use App\Enums\RefundStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\RefundRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RefundRequest>
 */
class RefundRequestFactory extends Factory
{
    protected $model = RefundRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'order_id' => Order::factory(),
            'requested_amount_minor' => fake()->numberBetween(1000, 40000),
            'currency' => 'USD',
            'reason' => RefundReason::DamagedItem,
            'customer_message' => fake()->sentence(12),
            'status' => RefundStatus::Pending,
            'idempotency_key' => (string) Str::uuid(),
        ];
    }
}
