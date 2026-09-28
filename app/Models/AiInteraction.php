<?php

namespace App\Models;

use Database\Factories\AiInteractionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $refund_request_id
 * @property string $provider
 * @property string $model
 * @property string|null $provider_request_id
 * @property string $status
 * @property array<string, mixed>|null $structured_output
 * @property string|null $error_message
 * @property int|null $latency_ms
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['refund_request_id', 'provider', 'model', 'provider_request_id', 'status', 'structured_output', 'error_message', 'latency_ms'])]
class AiInteraction extends Model
{
    /** @use HasFactory<AiInteractionFactory> */
    use HasFactory, HasUuids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'structured_output' => 'array',
            'latency_ms' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<RefundRequest, $this>
     */
    public function refundRequest(): BelongsTo
    {
        return $this->belongsTo(RefundRequest::class);
    }
}
