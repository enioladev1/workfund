<?php

namespace Database\Factories;

use App\Models\AiInteraction;
use App\Models\RefundRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiInteraction>
 */
class AiInteractionFactory extends Factory
{
    protected $model = AiInteraction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'refund_request_id' => RefundRequest::factory(),
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'provider_request_id' => (string) fake()->uuid(),
            'status' => 'success',
            'structured_output' => [
                'classification' => 'damaged_item',
                'confidence' => 0.9,
                'suspicious' => false,
                'conflict_detected' => false,
                'reasoning' => 'Customer reports the item arrived damaged.',
                'recommended_action' => 'approve',
            ],
            'error_message' => null,
            'latency_ms' => fake()->numberBetween(200, 2000),
        ];
    }
}
