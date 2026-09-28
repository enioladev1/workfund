<?php

namespace App\Services\Refund;

use App\Enums\DecisionType;
use App\Services\AI\DTOs\RefundAiAnalysis;
use App\Support\PolicyEvaluationResult;
use App\Support\RefundDecisionResult;

/**
 * Combines the (authoritative) policy result with the AI's (advisory) analysis
 * into a final decision. A hard policy decision always wins, no matter what the
 * AI recommends: this is the enforcement point for "AI cannot override policy".
 */
class RefundDecisionService
{
    private const MIN_APPROVE_CONFIDENCE = 0.5;

    public function decide(
        PolicyEvaluationResult $policyResult,
        ?RefundAiAnalysis $aiAnalysis,
        bool $injectionDetected,
    ): RefundDecisionResult {
        if ($policyResult->isHardDecided()) {
            return new RefundDecisionResult(
                decision: $policyResult->hardDecision,
                policyResult: $policyResult,
                aiAnalysis: $aiAnalysis,
                internalReasoning: $policyResult->hardDecisionReason ?? '',
                injectionDetected: $injectionDetected,
            );
        }

        if ($injectionDetected) {
            return new RefundDecisionResult(
                decision: DecisionType::Escalated,
                policyResult: $policyResult,
                aiAnalysis: $aiAnalysis,
                internalReasoning: "The customer's message contained a potential prompt-injection or policy-override attempt and requires manual review.",
                injectionDetected: true,
            );
        }

        if ($aiAnalysis === null) {
            return new RefundDecisionResult(
                decision: DecisionType::Escalated,
                policyResult: $policyResult,
                aiAnalysis: null,
                internalReasoning: 'Automated AI review was unavailable or returned an invalid response, so this request was sent for manual review.',
                injectionDetected: false,
            );
        }

        if ($aiAnalysis->suspicious || $aiAnalysis->conflictDetected) {
            return new RefundDecisionResult(
                decision: DecisionType::Escalated,
                policyResult: $policyResult,
                aiAnalysis: $aiAnalysis,
                internalReasoning: $aiAnalysis->reasoning,
                injectionDetected: false,
            );
        }

        return match ($aiAnalysis->recommendedAction) {
            'deny' => new RefundDecisionResult(
                decision: DecisionType::Denied,
                policyResult: $policyResult,
                aiAnalysis: $aiAnalysis,
                internalReasoning: $aiAnalysis->reasoning,
                injectionDetected: false,
            ),
            'approve' => $aiAnalysis->confidence >= self::MIN_APPROVE_CONFIDENCE
                ? new RefundDecisionResult(
                    decision: DecisionType::Approved,
                    policyResult: $policyResult,
                    aiAnalysis: $aiAnalysis,
                    internalReasoning: $aiAnalysis->reasoning,
                    injectionDetected: false,
                )
                : new RefundDecisionResult(
                    decision: DecisionType::Escalated,
                    policyResult: $policyResult,
                    aiAnalysis: $aiAnalysis,
                    internalReasoning: 'AI recommended approval with low confidence, so this request was sent for manual review.',
                    injectionDetected: false,
                ),
            default => new RefundDecisionResult(
                decision: DecisionType::Escalated,
                policyResult: $policyResult,
                aiAnalysis: $aiAnalysis,
                internalReasoning: $aiAnalysis->reasoning,
                injectionDetected: false,
            ),
        };
    }
}
