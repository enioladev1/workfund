<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = fake()->numberBetween(1000, 30000);
        $quantity = fake()->numberBetween(1, 3);

        return [
            'order_id' => Order::factory(),
            'sku' => strtoupper(fake()->bothify('SKU-####??')),
            'name' => fake()->words(3, true),
            'quantity' => $quantity,
            'unit_price_minor' => $unitPrice,
            'total_price_minor' => $unitPrice * $quantity,
            'is_final_sale' => false,
        ];
    }

    public function finalSale(): static
    {
        return $this->state(fn () => ['is_final_sale' => true]);
    }
}
