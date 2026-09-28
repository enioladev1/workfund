<?php

namespace App\Support;

readonly class PolicyRuleResult
{
    public function __construct(
        public string $key,
        public string $label,
        public bool $passed,
        public string $message,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'passed' => $this->passed,
            'message' => $this->message,
        ];
    }
}
