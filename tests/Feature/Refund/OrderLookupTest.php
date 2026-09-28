<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;

test('a customer can look up their own order by email and order number', function () {
    $customer = Customer::factory()->create(['email' => 'owner@example.test']);
    $order = Order::factory()->for($customer)->create(['order_number' => 'WF-LOOKUP1']);
    OrderItem::factory()->for($order)->create();

    $response = $this->postJson('/api/orders/lookup', [
        'email' => 'owner@example.test',
        'order_number' => 'WF-LOOKUP1',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.order_number', 'WF-LOOKUP1');
    expect($response->json('data.items'))->not->toBeEmpty();
});

test('an order number cannot be looked up with the wrong email (IDOR protection)', function () {
    $owner = Customer::factory()->create(['email' => 'realowner@example.test']);
    Order::factory()->for($owner)->create(['order_number' => 'WF-LOOKUP2']);

    Customer::factory()->create(['email' => 'attacker@example.test']);

    $response = $this->postJson('/api/orders/lookup', [
        'email' => 'attacker@example.test',
        'order_number' => 'WF-LOOKUP2',
    ]);

    $response->assertNotFound();
});

test('order lookup validates required fields', function () {
    $response = $this->postJson('/api/orders/lookup', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email', 'order_number']);
});
