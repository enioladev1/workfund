<?php

namespace App\Services\Refund;

use App\Enums\ActorType;
use App\Enums\AuditAction;
use App\Models\AuditLog;

/**
 * The only way audit_logs rows get created. There is no update/delete path:
 * see AuditLog::booted() for the model-level enforcement of that.
 */
class AuditLogService
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        string $entityType,
        string $entityId,
        AuditAction $action,
        ActorType $actorType = ActorType::System,
        ?string $actorId = null,
        array $metadata = [],
    ): AuditLog {
        return AuditLog::create([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action->value,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'metadata' => $metadata,
        ]);
    }
}
