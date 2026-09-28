<?php

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\RefundDecision;
use App\Models\RefundRequest;
use App\Models\User;

test('guests cannot access the admin refunds api', function () {
    $this->getJson('/api/admin/refunds')->assertUnauthorized();
});

test('an authenticated staff member can list refund requests', function () {
    $staff = User::factory()->create();
    RefundRequest::factory()->count(3)->create();

    $response = $this->actingAs($staff)->getJson('/api/admin/refunds');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(3);
});

test('the refund list can be filtered by status', function () {
    $staff = User::factory()->create();
    RefundRequest::factory()->create(['status' => 'approved']);
    RefundRequest::factory()->create(['status' => 'denied']);

    $response = $this->actingAs($staff)->getJson('/api/admin/refunds?status=approved');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.status'))->toBe('approved');
});

test('an authenticated staff member can view refund detail with policy and ai analysis', function () {
    $staff = User::factory()->create();
    $refundRequest = RefundRequest::factory()->create();
    RefundDecision::factory()->for($refundRequest)->create([
        'policy_result' => ['rules' => [['key' => 'final_sale', 'label' => 'Final sale', 'passed' => true, 'message' => 'ok']]],
    ]);

    $response = $this->actingAs($staff)->getJson("/api/admin/refunds/{$refundRequest->id}");

    $response->assertOk();
    $response->assertJsonPath('data.id', $refundRequest->id);
    expect($response->json('data.decisions.0.policy_result.rules'))->not->toBeEmpty();
});

test('viewing a refund detail records an audit log entry', function () {
    $staff = User::factory()->create();
    $refundRequest = RefundRequest::factory()->create();

    $this->actingAs($staff)->getJson("/api/admin/refunds/{$refundRequest->id}");

    $this->assertDatabaseHas('audit_logs', [
        'entity_type' => 'refund_request',
        'entity_id' => $refundRequest->id,
        'action' => 'admin.viewed_refund',
    ]);
});

test('the audit trail endpoint returns entries in chronological order', function () {
    $staff = User::factory()->create();
    $refundRequest = RefundRequest::factory()->create();

    AuditLog::create(['entity_type' => 'refund_request', 'entity_id' => $refundRequest->id, 'action' => 'refund.requested', 'actor_type' => 'customer']);
    AuditLog::create(['entity_type' => 'refund_request', 'entity_id' => $refundRequest->id, 'action' => 'refund.approved', 'actor_type' => 'system']);

    $response = $this->actingAs($staff)->getJson("/api/admin/refunds/{$refundRequest->id}/audit");

    $response->assertOk();
    expect($response->json('data.0.action'))->toBe('refund.requested');
    expect($response->json('data.1.action'))->toBe('refund.approved');
});

test('staff can manually resolve an escalated refund request', function () {
    $staff = User::factory()->create();
    $refundRequest = RefundRequest::factory()->create(['status' => 'escalated']);
    RefundDecision::factory()->for($refundRequest)->create(['decision' => 'escalated']);

    $response = $this->actingAs($staff)->patchJson("/api/admin/refunds/{$refundRequest->id}/resolve", [
        'decision' => 'approved',
        'reasoning' => 'Reviewed manually and confirmed the damage claim with photos.',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.status', 'approved');
    $this->assertDatabaseHas('refund_requests', ['id' => $refundRequest->id, 'status' => 'approved']);
    $this->assertDatabaseHas('refund_decisions', ['refund_request_id' => $refundRequest->id, 'decision' => 'approved', 'decided_by' => 'staff', 'decided_by_user_id' => $staff->id]);
});

test('a non-escalated refund request cannot be manually resolved', function () {
    $staff = User::factory()->create();
    $refundRequest = RefundRequest::factory()->create(['status' => 'approved']);

    $response = $this->actingAs($staff)->patchJson("/api/admin/refunds/{$refundRequest->id}/resolve", [
        'decision' => 'denied',
        'reasoning' => 'Trying to change an already-decided request.',
    ]);

    $response->assertUnprocessable();
    $this->assertDatabaseHas('refund_requests', ['id' => $refundRequest->id, 'status' => 'approved']);
});

test('resolve requires a decision and reasoning', function () {
    $staff = User::factory()->create();
    $refundRequest = RefundRequest::factory()->create(['status' => 'escalated']);

    $response = $this->actingAs($staff)->patchJson("/api/admin/refunds/{$refundRequest->id}/resolve", []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['decision', 'reasoning']);
});

test('customer A cannot access customer Bs refund via the admin id alone without auth', function () {
    $customerA = Customer::factory()->create();
    $order = Order::factory()->for($customerA)->create();
    $refundRequest = RefundRequest::factory()->for($customerA)->for($order)->create();

    // No session at all: must be rejected before any data is returned.
    $this->getJson("/api/admin/refunds/{$refundRequest->id}")->assertUnauthorized();
});
