<?php

namespace App\Http\Resources;

use App\Models\RefundDecision;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RefundDecision
 */
class RefundDecisionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'decision' => $this->decision->value,
            'policy_result' => $this->policy_result,
            'ai_result' => $this->ai_result,
            'reasoning' => $this->reasoning,
            'decided_by' => $this->decided_by->value,
            'decided_by_user' => $this->whenLoaded('decidedByUser', fn () => $this->decidedByUser ? [
                'name' => $this->decidedByUser->name,
            ] : null),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
