<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests cannot access the admin customers page', function () {
    $this->get('/admin/customers')->assertRedirect('/login');
});

test('the customers index page lists customers with order counts', function () {
    $staff = User::factory()->create();
    $customer = Customer::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.test']);
    Order::factory()->for($customer)->count(2)->create();

    $response = $this->actingAs($staff)->get('/admin/customers');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/customers/index')
        ->where('customers.0.name', 'Jane Doe')
        ->where('customers.0.orders_count', 2),
    );
});

test('the customers index can be filtered by email', function () {
    $staff = User::factory()->create();
    Customer::factory()->create(['email' => 'match-target@example.test']);
    Customer::factory()->create(['email' => 'no-match@example.test']);

    $response = $this->actingAs($staff)->get('/admin/customers?email=match-target');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page->has('customers', 1));
});

test('guests cannot use the customer search api', function () {
    $this->getJson('/api/admin/customers/search?q=test')->assertUnauthorized();
});

test('the customer search api returns matches by name or email', function () {
    $staff = User::factory()->create();
    Customer::factory()->create(['name' => 'Searchable Sam', 'email' => 'sam@example.test']);
    Customer::factory()->create(['name' => 'Someone Else', 'email' => 'else@example.test']);

    $response = $this->actingAs($staff)->getJson('/api/admin/customers/search?q=searchable');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toBe('Searchable Sam');
});

test('the customer search api returns nothing for an empty query', function () {
    $staff = User::factory()->create();
    Customer::factory()->create();

    $response = $this->actingAs($staff)->getJson('/api/admin/customers/search');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(0);
});
