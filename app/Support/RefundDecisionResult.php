<?php

namespace App\Support;

use App\Enums\DecisionType;
use App\Services\AI\DTOs\RefundAiAnalysis;

readonly class RefundDecisionResult
{
    public function __construct(
        public DecisionType $decision,
        public PolicyEvaluationResult $policyResult,
        public ?RefundAiAnalysis $aiAnalysis,
        public string $internalReasoning,
        public bool $injectionDetected,
    ) {}
}
