<?php

use App\Models\AuditLog;
use Illuminate\Support\Str;

test('an audit log record can be created', function () {
    $log = AuditLog::create([
        'entity_type' => 'refund_request',
        'entity_id' => (string) Str::uuid(),
        'action' => 'refund.requested',
        'actor_type' => 'customer',
    ]);

    $this->assertDatabaseHas('audit_logs', ['id' => $log->id]);
});

test('an audit log record cannot be updated through the application', function () {
    $log = AuditLog::factory()->create();

    $log->action = 'tampered';

    expect(fn () => $log->save())->toThrow(LogicException::class);
});

test('an audit log record cannot be deleted through the application', function () {
    $log = AuditLog::factory()->create();

    expect(fn () => $log->delete())->toThrow(LogicException::class);
    $this->assertDatabaseHas('audit_logs', ['id' => $log->id]);
});

test('there is no route that allows updating or deleting an audit log', function () {
    $log = AuditLog::factory()->create();

    $this->patchJson("/api/admin/audit-logs/{$log->id}")->assertNotFound();
    $this->deleteJson("/api/admin/audit-logs/{$log->id}")->assertNotFound();
});
