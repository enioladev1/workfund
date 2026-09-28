<?php

namespace App\Http\Resources\Customer;

use App\Models\RefundDecision;
use App\Models\RefundRequest;
use App\Services\AI\DTOs\RefundAiAnalysis;
use App\Services\Refund\CustomerMessageBuilder;
use App\Support\Money;
use App\Support\PolicyEvaluationResult;
use App\Support\PolicyRuleResult;
use App\Support\RefundDecisionResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Deliberately minimal: never includes policy_result, ai_result, or internal
 * reasoning. Customers only ever see status + a plain-English explanation.
 *
 * @mixin RefundRequest
 */
class RefundStatusResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $latestDecision = $this->latestDecision();

        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'requested_amount' => Money::toDecimalString($this->requested_amount_minor),
            'currency' => $this->currency,
            'message' => $latestDecision ? $this->messageFor($latestDecision) : 'Your refund request is still being processed.',
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }

    private function messageFor(RefundDecision $decision): string
    {
        $rules = array_values(array_map(
            fn (array $rule) => new PolicyRuleResult($rule['key'], $rule['label'], $rule['passed'], $rule['message']),
            $decision->policy_result['rules'] ?? [],
        ));

        $aiResult = $decision->ai_result;

        $aiAnalysis = $aiResult ? new RefundAiAnalysis(
            classification: $aiResult['classification'],
            confidence: (float) $aiResult['confidence'],
            suspicious: (bool) $aiResult['suspicious'],
            conflictDetected: (bool) $aiResult['conflict_detected'],
            reasoning: $aiResult['reasoning'],
            recommendedAction: $aiResult['recommended_action'],
        ) : null;

        $decisionResult = new RefundDecisionResult(
            decision: $decision->decision,
            policyResult: new PolicyEvaluationResult(rules: $rules, hardDecision: null, hardDecisionReason: null),
            aiAnalysis: $aiAnalysis,
            internalReasoning: '',
            injectionDetected: false,
        );

        return app(CustomerMessageBuilder::class)->build($decisionResult);
    }
}
