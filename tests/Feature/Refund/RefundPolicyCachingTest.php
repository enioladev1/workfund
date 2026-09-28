<?php

use App\Models\RefundPolicyRule;
use App\Services\Refund\RefundPolicyService;
use Illuminate\Support\Facades\Cache;

test('policy rules fall back to config defaults when the database table is empty', function () {
    $service = app(RefundPolicyService::class);

    $rules = $service->rules();

    expect($rules['refund_window_days'])->toBe(config('refund_policy.refund_window_days'));
    expect($rules['human_review_threshold_minor'])->toBe(config('refund_policy.human_review_threshold_minor'));
});

test('policy rules reflect database values once seeded', function () {
    RefundPolicyRule::create([
        'key' => 'refund_window_days',
        'value' => ['value' => 14],
        'description' => 'test override',
    ]);

    $service = app(RefundPolicyService::class);

    expect($service->rules()['refund_window_days'])->toBe(14);
});

test('policy rules are cached so repeated calls do not hit the database', function () {
    $service = app(RefundPolicyService::class);
    $service->rules();

    expect(Cache::has('refund_policy:rules'))->toBeTrue();
});

test('forgetting the cache allows fresh values to be read', function () {
    $service = app(RefundPolicyService::class);
    $service->rules();

    RefundPolicyRule::create([
        'key' => 'refund_window_days',
        'value' => ['value' => 7],
        'description' => 'updated',
    ]);

    $service->forgetCache();

    expect($service->rules()['refund_window_days'])->toBe(7);
});
