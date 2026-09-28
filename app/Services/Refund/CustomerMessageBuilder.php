<?php

namespace App\Services\Refund;

use App\Enums\DecisionType;
use App\Support\PolicyRuleResult;
use App\Support\RefundDecisionResult;

/**
 * Produces the simple-English message shown to the customer. Deliberately never
 * renders raw AI reasoning or internal policy detail: only this template's own
 * wording reaches the customer, based on which rule/signal drove the decision.
 */
class CustomerMessageBuilder
{
    public function build(RefundDecisionResult $result): string
    {
        return match ($result->decision) {
            DecisionType::Approved => $this->approvedMessage($result),
            DecisionType::Denied => $this->deniedMessage($result),
            DecisionType::Escalated => $this->escalatedMessage(),
        };
    }

    private function approvedMessage(RefundDecisionResult $result): string
    {
        $reasonPhrase = match ($result->aiAnalysis?->classification) {
            'damaged_item' => 'the item was reported as damaged',
            'incorrect_item' => 'the wrong item was received',
            'not_as_described' => 'the item did not match its description',
            default => 'your request met our refund policy',
        };

        return "Your refund request was approved because {$reasonPhrase} and the order is within the refund period.";
    }

    private function deniedMessage(RefundDecisionResult $result): string
    {
        $failedRule = $this->firstFailedRule($result);

        return match ($failedRule?->key) {
            'final_sale' => 'This order is marked as final sale and is not eligible for a refund under the current refund policy.',
            'refund_window' => 'This order is outside the refund window, so it is not eligible for a refund.',
            'not_already_refunded' => 'This order has already been refunded.',
            default => 'This request does not qualify for a refund under our current policy.',
        };
    }

    private function escalatedMessage(): string
    {
        return 'A support specialist needs to review this request before a final decision can be made.';
    }

    private function firstFailedRule(RefundDecisionResult $result): ?PolicyRuleResult
    {
        foreach ($result->policyResult->rules as $rule) {
            if (! $rule->passed) {
                return $rule;
            }
        }

        return null;
    }
}
