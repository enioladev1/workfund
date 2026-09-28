<?php

namespace App\Services\AI\DTOs;

/**
 * Validated, structured AI output. Never constructed directly from raw provider
 * output; only AiResponseValidator::validate() may produce one of these.
 */
readonly class RefundAiAnalysis
{
    public function __construct(
        public string $classification,
        public float $confidence,
        public bool $suspicious,
        public bool $conflictDetected,
        public string $reasoning,
        public string $recommendedAction,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'classification' => $this->classification,
            'confidence' => $this->confidence,
            'suspicious' => $this->suspicious,
            'conflict_detected' => $this->conflictDetected,
            'reasoning' => $this->reasoning,
            'recommended_action' => $this->recommendedAction,
        ];
    }
}
