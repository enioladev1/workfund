<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RefundRequest;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the dashboard page renders stats and recent refunds with resolved customer data', function () {
    $staff = User::factory()->create();
    $customer = Customer::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.test']);
    $order = Order::factory()->for($customer)->create();
    RefundRequest::factory()->for($customer)->for($order)->create(['status' => 'approved']);

    $response = $this->actingAs($staff)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('dashboard')
        ->has('stats')
        ->has('recent', 1)
        // Regression guard: nested resources must resolve to plain values, never
        // a stray {"data": {...}} wrapper from an unresolved nested JsonResource.
        ->where('recent.0.customer.name', 'Jane Doe')
        ->where('recent.0.customer.email', 'jane@example.test')
        ->where('recent.0.order.order_number', $order->order_number),
    );
});

test('the admin refund detail page resolves nested order items without a stray data wrapper', function () {
    $staff = User::factory()->create();
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create();
    $item = OrderItem::factory()->for($order)->create(['name' => 'Widget']);
    $refundRequest = RefundRequest::factory()->for($customer)->for($order)->create(['order_item_id' => $item->id]);

    $response = $this->actingAs($staff)->get("/admin/refunds/{$refundRequest->id}");

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/refunds/show')
        ->where('refund.order.order_number', $order->order_number)
        ->where('refund.order_item.name', 'Widget'),
    );
});
