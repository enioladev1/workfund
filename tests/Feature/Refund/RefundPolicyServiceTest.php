<?php

use App\Enums\DecisionType;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RefundRequest;
use App\Services\Refund\RefundPolicyService;

beforeEach(function () {
    $this->policy = app(RefundPolicyService::class);
});

test('final sale items are denied regardless of other factors', function () {
    $order = Order::factory()->create(['is_final_sale' => true, 'total_minor' => 10000]);

    $result = $this->policy->evaluate($order, null, 5000);

    expect($result->hardDecision)->toBe(DecisionType::Denied);
    expect(collect($result->rules)->firstWhere('key', 'final_sale')->passed)->toBeFalse();
});

test('an order item marked final sale overrides a non-final-sale order', function () {
    $order = Order::factory()->create(['is_final_sale' => false, 'total_minor' => 10000]);
    $item = OrderItem::factory()->for($order)->create(['is_final_sale' => true, 'total_price_minor' => 5000]);

    $result = $this->policy->evaluate($order, $item, 5000);

    expect($result->hardDecision)->toBe(DecisionType::Denied);
});

test('orders older than the refund window are denied', function () {
    $order = Order::factory()->orderedDaysAgo(45)->create(['total_minor' => 10000]);

    $result = $this->policy->evaluate($order, null, 5000);

    expect($result->hardDecision)->toBe(DecisionType::Denied);
    expect(collect($result->rules)->firstWhere('key', 'refund_window')->passed)->toBeFalse();
});

test('orders within the refund window pass that rule', function () {
    $order = Order::factory()->orderedDaysAgo(5)->create(['total_minor' => 10000, 'is_final_sale' => false]);

    $result = $this->policy->evaluate($order, null, 5000);

    expect(collect($result->rules)->firstWhere('key', 'refund_window')->passed)->toBeTrue();
});

test('refunds above the human review threshold are escalated', function () {
    $order = Order::factory()->orderedDaysAgo(2)->create(['total_minor' => 100000, 'is_final_sale' => false]);

    $result = $this->policy->evaluate($order, null, 60000);

    expect($result->hardDecision)->toBe(DecisionType::Escalated);
    expect(collect($result->rules)->firstWhere('key', 'refund_threshold')->passed)->toBeFalse();
});

test('refunds at or below the threshold pass that rule', function () {
    $order = Order::factory()->orderedDaysAgo(2)->create(['total_minor' => 100000, 'is_final_sale' => false]);

    $result = $this->policy->evaluate($order, null, 50000);

    expect(collect($result->rules)->firstWhere('key', 'refund_threshold')->passed)->toBeTrue();
});

test('an order already refunded in full is denied for a new request', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->orderedDaysAgo(2)->create(['total_minor' => 10000, 'is_final_sale' => false]);

    RefundRequest::factory()->for($customer)->for($order)->create([
        'requested_amount_minor' => 10000,
        'status' => 'approved',
    ]);

    $result = $this->policy->evaluate($order, null, 5000);

    expect($result->hardDecision)->toBe(DecisionType::Denied);
    expect(collect($result->rules)->firstWhere('key', 'not_already_refunded')->passed)->toBeFalse();
});

test('a request that passes every rule has no hard decision', function () {
    $order = Order::factory()->orderedDaysAgo(2)->create(['total_minor' => 10000, 'is_final_sale' => false]);

    $result = $this->policy->evaluate($order, null, 5000);

    expect($result->hardDecision)->toBeNull();
    expect(collect($result->rules)->every(fn ($rule) => $rule->passed))->toBeTrue();
});
