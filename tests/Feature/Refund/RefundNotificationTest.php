<?php

use App\Mail\RefundDecisionMail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RefundRequest;
use App\Services\AI\Contracts\AiProviderInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Tests\Fixtures\FakeAiProvider;

test('a customer notification email is queued after a decision is made', function () {
    Mail::fake();

    app()->bind(AiProviderInterface::class, fn () => FakeAiProvider::returning([
        'classification' => 'damaged_item',
        'confidence' => 0.9,
        'suspicious' => false,
        'conflict_detected' => false,
        'reasoning' => 'ok',
        'recommended_action' => 'approve',
    ]));

    $customer = Customer::factory()->create(['email' => 'notifyme@example.test']);
    $order = Order::factory()->for($customer)->orderedDaysAgo(2)->create(['order_number' => 'WF-NOTIFY1', 'total_minor' => 5000]);
    $item = OrderItem::factory()->for($order)->create(['total_price_minor' => 5000]);

    $this->postJson('/api/refunds', [
        'email' => 'notifyme@example.test',
        'order_number' => 'WF-NOTIFY1',
        'order_item_id' => $item->id,
        'reason' => 'damaged_item',
        'customer_message' => 'It arrived broken.',
    ])->assertCreated();

    Mail::assertQueued(RefundDecisionMail::class, function (RefundDecisionMail $mail) use ($customer) {
        return $mail->hasTo($customer->email);
    });
});

test('the notification mailable implements ShouldQueue so it never blocks the request', function () {
    expect(new RefundDecisionMail(
        RefundRequest::factory()->make(),
        'test message',
    ))->toBeInstanceOf(ShouldQueue::class);
});

test('the notification mail actually renders without error', function () {
    $refundRequest = RefundRequest::factory()->create(['status' => 'approved']);

    $html = (new RefundDecisionMail($refundRequest, 'Your refund was approved.'))->render();

    expect($html)->toContain('Your refund was approved.');
    expect($html)->toContain($refundRequest->id);
});
