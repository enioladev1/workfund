<?php

namespace App\Models;

use App\Enums\ActorType;
use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * Append-only audit trail. Updating or deleting a row is blocked at the model
 * level (in addition to no route ever exposing those actions).
 *
 * @property string $id
 * @property string $entity_type
 * @property string $entity_id
 * @property string $action
 * @property ActorType $actor_type
 * @property string|null $actor_id
 * @property array<string, mixed>|null $metadata
 * @property Carbon $created_at
 */
#[Fillable(['entity_type', 'entity_id', 'action', 'actor_type', 'actor_id', 'metadata'])]
class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory, HasUuids;

    const ?string UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'actor_type' => ActorType::class,
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('Audit logs are append-only and cannot be updated.');
        });

        static::deleting(function () {
            throw new LogicException('Audit logs are append-only and cannot be deleted.');
        });
    }

    public function entity(): ?Model
    {
        $modelClass = match ($this->entity_type) {
            'refund_request' => RefundRequest::class,
            'order' => Order::class,
            'customer' => Customer::class,
            default => null,
        };

        return $modelClass ? $modelClass::find($this->entity_id) : null;
    }
}
