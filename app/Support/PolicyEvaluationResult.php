<?php

namespace App\Support;

use App\Enums\DecisionType;

readonly class PolicyEvaluationResult
{
    /**
     * @param  list<PolicyRuleResult>  $rules
     */
    public function __construct(
        public array $rules,
        public ?DecisionType $hardDecision,
        public ?string $hardDecisionReason,
    ) {}

    public function isHardDecided(): bool
    {
        return $this->hardDecision !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'rules' => array_map(fn (PolicyRuleResult $rule) => $rule->toArray(), $this->rules),
            'hard_decision' => $this->hardDecision?->value,
            'hard_decision_reason' => $this->hardDecisionReason,
        ];
    }
}
