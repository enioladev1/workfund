<?php

namespace App\Services\AI;

use App\Services\AI\DTOs\RefundAiAnalysis;
use Illuminate\Support\Str;

/**
 * The only place raw AI text becomes a trusted RefundAiAnalysis. Anything that
 * doesn't match the expected shape exactly is rejected, never guessed at.
 */
class AiResponseValidator
{
    private const ALLOWED_CLASSIFICATIONS = [
        'damaged_item', 'incorrect_item', 'not_as_described', 'changed_mind', 'no_longer_needed', 'other',
    ];

    private const ALLOWED_ACTIONS = ['approve', 'deny', 'escalate'];

    public function validate(string $rawContent): ?RefundAiAnalysis
    {
        $json = $this->extractJson($rawContent);

        if ($json === null) {
            return null;
        }

        if (! $this->hasValidShape($json)) {
            return null;
        }

        return new RefundAiAnalysis(
            classification: $json['classification'],
            confidence: (float) $json['confidence'],
            suspicious: (bool) $json['suspicious'],
            conflictDetected: (bool) $json['conflict_detected'],
            reasoning: Str::limit((string) $json['reasoning'], 1000, ''),
            recommendedAction: $json['recommended_action'],
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function extractJson(string $rawContent): ?array
    {
        $trimmed = trim($rawContent);

        // Models sometimes wrap JSON in markdown fences despite instructions not to.
        if (str_starts_with($trimmed, '```')) {
            $trimmed = preg_replace('/^```(json)?/i', '', $trimmed);
            $trimmed = preg_replace('/```$/', '', trim($trimmed));
            $trimmed = trim($trimmed);
        }

        $decoded = json_decode($trimmed, associative: true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param  array<string, mixed>  $json
     */
    private function hasValidShape(array $json): bool
    {
        if (! isset($json['classification'], $json['confidence'], $json['suspicious'], $json['conflict_detected'], $json['reasoning'], $json['recommended_action'])) {
            return false;
        }

        if (! in_array($json['classification'], self::ALLOWED_CLASSIFICATIONS, true)) {
            return false;
        }

        if (! in_array($json['recommended_action'], self::ALLOWED_ACTIONS, true)) {
            return false;
        }

        if (! is_numeric($json['confidence'])) {
            return false;
        }

        $confidence = (float) $json['confidence'];

        if ($confidence < 0.0 || $confidence > 1.0) {
            return false;
        }

        if (! is_bool($json['suspicious']) || ! is_bool($json['conflict_detected'])) {
            return false;
        }

        if (! is_string($json['reasoning'])) {
            return false;
        }

        return true;
    }
}
