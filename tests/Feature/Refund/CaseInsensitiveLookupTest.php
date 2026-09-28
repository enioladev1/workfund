<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RefundRequest;

test('order lookup matches an email typed with different casing', function () {
    $customer = Customer::factory()->create(['email' => 'lowercase@example.test']);
    Order::factory()->for($customer)->create(['order_number' => 'WF-CASE01']);

    $response = $this->postJson('/api/orders/lookup', [
        'email' => 'LowerCase@Example.Test',
        'order_number' => 'WF-CASE01',
    ]);

    $response->assertOk();
});

test('order lookup matches an order number typed with different casing', function () {
    $customer = Customer::factory()->create(['email' => 'casetest@example.test']);
    Order::factory()->for($customer)->create(['order_number' => 'WF-CASE02']);

    $response = $this->postJson('/api/orders/lookup', [
        'email' => 'casetest@example.test',
        'order_number' => 'wf-case02',
    ]);

    $response->assertOk();
});

test('refund status lookup matches an email typed with different casing', function () {
    $customer = Customer::factory()->create(['email' => 'statuscase@example.test']);
    $order = Order::factory()->for($customer)->create();
    OrderItem::factory()->for($order)->create();
    $refundRequest = RefundRequest::factory()->for($customer)->for($order)->create();

    $response = $this->getJson("/api/refunds/{$refundRequest->id}?email=StatusCase@Example.Test");

    $response->assertOk();
});
