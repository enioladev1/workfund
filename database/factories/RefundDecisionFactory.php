<?php

namespace Database\Factories;

use App\Enums\DecidedBy;
use App\Enums\DecisionType;
use App\Models\RefundDecision;
use App\Models\RefundRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RefundDecision>
 */
class RefundDecisionFactory extends Factory
{
    protected $model = RefundDecision::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'refund_request_id' => RefundRequest::factory(),
            'decision' => DecisionType::Approved,
            'policy_result' => ['rules' => []],
            'ai_result' => null,
            'reasoning' => fake()->sentence(),
            'decided_by' => DecidedBy::System,
            'decided_by_user_id' => null,
        ];
    }
}
