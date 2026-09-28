<?php

namespace Database\Factories;

use App\Models\RefundPolicyRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RefundPolicyRule>
 */
class RefundPolicyRuleFactory extends Factory
{
    protected $model = RefundPolicyRule::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->word(),
            'value' => ['enabled' => true],
            'description' => fake()->sentence(),
        ];
    }
}
