<?php

namespace App\Models;

use App\Enums\DecidedBy;
use App\Enums\DecisionType;
use Database\Factories\RefundDecisionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $refund_request_id
 * @property DecisionType $decision
 * @property array<string, mixed> $policy_result
 * @property array<string, mixed>|null $ai_result
 * @property string $reasoning
 * @property DecidedBy $decided_by
 * @property int|null $decided_by_user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['refund_request_id', 'decision', 'policy_result', 'ai_result', 'reasoning', 'decided_by', 'decided_by_user_id'])]
class RefundDecision extends Model
{
    /** @use HasFactory<RefundDecisionFactory> */
    use HasFactory, HasUuids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'decision' => DecisionType::class,
            'decided_by' => DecidedBy::class,
            'policy_result' => 'array',
            'ai_result' => 'array',
        ];
    }

    /**
     * @return BelongsTo<RefundRequest, $this>
     */
    public function refundRequest(): BelongsTo
    {
        return $this->belongsTo(RefundRequest::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function decidedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }
}
