<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

afterEach(function () {
    RateLimiter::clear('login|127.0.0.1');
});

test('refund submission is rate limited', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/refunds', [])->assertStatus(422);
    }

    $response = $this->postJson('/api/refunds', []);

    $response->assertStatus(429);
    $response->assertJson(['success' => false]);
    expect($response->json('message'))->toContain('too quickly');
});

test('order lookup is rate limited', function () {
    for ($i = 0; $i < 15; $i++) {
        $this->postJson('/api/orders/lookup', [])->assertStatus(422);
    }

    $this->postJson('/api/orders/lookup', [])->assertStatus(429);
});

test('login is rate limited after repeated failures', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/login', ['email' => $user->email, 'password' => 'wrong-password']);
    }

    $response = $this->postJson('/login', ['email' => $user->email, 'password' => 'wrong-password']);

    $response->assertStatus(429);
});
