<?php

namespace Database\Factories;

use App\Enums\ActorType;
use App\Enums\AuditAction;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entity_type' => 'refund_request',
            'entity_id' => (string) Str::uuid(),
            'action' => AuditAction::RefundRequested->value,
            'actor_type' => ActorType::System,
            'actor_id' => null,
            'metadata' => [],
        ];
    }
}
