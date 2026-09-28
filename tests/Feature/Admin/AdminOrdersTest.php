<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests cannot access the admin orders pages', function () {
    $this->get('/admin/orders')->assertRedirect('/login');
    $this->get('/admin/orders/create')->assertRedirect('/login');
});

test('the orders index page lists orders with customer info', function () {
    $staff = User::factory()->create();
    $customer = Customer::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.test']);
    Order::factory()->for($customer)->create(['order_number' => 'WF-999001']);

    $response = $this->actingAs($staff)->get('/admin/orders');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/orders/index')
        ->where('orders.0.order_number', 'WF-999001')
        ->where('orders.0.customer.name', 'Jane Doe'),
    );
});

test('the orders index can be filtered by order number', function () {
    $staff = User::factory()->create();
    Order::factory()->create(['order_number' => 'WF-111111']);
    Order::factory()->create(['order_number' => 'WF-222222']);

    $response = $this->actingAs($staff)->get('/admin/orders?order_number=111111');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page->has('orders', 1));
});

test('an admin can create an order for an existing customer', function () {
    $staff = User::factory()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($staff)->post('/admin/orders', [
        'customer_id' => $customer->id,
        'order_number' => 'WF-777001',
        'currency' => 'USD',
        'is_final_sale' => false,
        'ordered_at' => now()->subDays(2)->toDateString(),
        'items' => [
            ['name' => 'Test Widget', 'sku' => 'WID-1', 'quantity' => 2, 'unit_price' => '25.50', 'is_final_sale' => false],
        ],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('orders', ['order_number' => 'WF-777001', 'customer_id' => $customer->id, 'total_minor' => 5100]);
    $this->assertDatabaseHas('order_items', ['sku' => 'WID-1', 'quantity' => 2, 'unit_price_minor' => 2550, 'total_price_minor' => 5100]);
});

test('an admin can create an order with a brand new customer', function () {
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->post('/admin/orders', [
        'new_customer_name' => 'Brand New Customer',
        'new_customer_email' => 'brandnew@example.test',
        'currency' => 'USD',
        'is_final_sale' => false,
        'ordered_at' => now()->toDateString(),
        'items' => [
            ['name' => 'Widget', 'sku' => 'WID-2', 'quantity' => 1, 'unit_price' => '10.00', 'is_final_sale' => false],
        ],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('customers', ['email' => 'brandnew@example.test']);
    $this->assertDatabaseHas('orders', ['total_minor' => 1000]);
});

test('creating an order requires either an existing or a new customer', function () {
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->post('/admin/orders', [
        'currency' => 'USD',
        'is_final_sale' => false,
        'ordered_at' => now()->toDateString(),
        'items' => [
            ['name' => 'Widget', 'sku' => 'WID-3', 'quantity' => 1, 'unit_price' => '10.00', 'is_final_sale' => false],
        ],
    ]);

    $response->assertSessionHasErrors(['new_customer_name', 'new_customer_email']);
});

test('creating an order requires at least one item', function () {
    $staff = User::factory()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($staff)->post('/admin/orders', [
        'customer_id' => $customer->id,
        'currency' => 'USD',
        'is_final_sale' => false,
        'ordered_at' => now()->toDateString(),
        'items' => [],
    ]);

    $response->assertSessionHasErrors('items');
});

test('a duplicate order number is rejected', function () {
    $staff = User::factory()->create();
    $customer = Customer::factory()->create();
    Order::factory()->create(['order_number' => 'WF-DUPE01']);

    $response = $this->actingAs($staff)->post('/admin/orders', [
        'customer_id' => $customer->id,
        'order_number' => 'WF-DUPE01',
        'currency' => 'USD',
        'is_final_sale' => false,
        'ordered_at' => now()->toDateString(),
        'items' => [
            ['name' => 'Widget', 'sku' => 'WID-4', 'quantity' => 1, 'unit_price' => '10.00', 'is_final_sale' => false],
        ],
    ]);

    $response->assertStatus(500);
});

test('the order detail page shows items and refund totals', function () {
    $staff = User::factory()->create();
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create(['total_minor' => 10000]);
    OrderItem::factory()->for($order)->create(['name' => 'Widget', 'total_price_minor' => 10000]);

    $response = $this->actingAs($staff)->get("/admin/orders/{$order->id}");

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/orders/show')
        ->where('order.items.0.name', 'Widget')
        ->where('order.remaining_refundable', '100.00'),
    );
});
