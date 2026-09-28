<?php

namespace App\Http\Resources;

use App\Models\AiInteraction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AiInteraction
 */
class AiInteractionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'model' => $this->model,
            'status' => $this->status,
            'structured_output' => $this->structured_output,
            'error_message' => $this->error_message,
            'latency_ms' => $this->latency_ms,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
