<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $orderedAt = fake()->dateTimeBetween('-90 days', '-1 days');

        return [
            'customer_id' => Customer::factory(),
            'order_number' => 'WF-'.fake()->unique()->numerify('######'),
            'status' => OrderStatus::Delivered,
            'currency' => 'USD',
            'total_minor' => fake()->numberBetween(1500, 80000),
            'is_final_sale' => false,
            'ordered_at' => $orderedAt,
            'delivered_at' => (clone $orderedAt)->modify('+3 days'),
        ];
    }

    public function finalSale(): static
    {
        return $this->state(fn () => ['is_final_sale' => true]);
    }

    public function orderedDaysAgo(int $days): static
    {
        return $this->state(function () use ($days) {
            $orderedAt = now()->subDays($days);

            return [
                'ordered_at' => $orderedAt,
                'delivered_at' => $orderedAt->clone()->addDays(3),
            ];
        });
    }

    public function withNumber(?string $number = null): static
    {
        return $this->state(fn () => [
            'order_number' => $number ?? 'WF-'.Str::padLeft((string) fake()->unique()->numberBetween(1, 999999), 6, '0'),
        ]);
    }
}
