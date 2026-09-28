<?php

use App\Services\AI\AiSettingsService;
use App\Services\AI\Contracts\AiProviderInterface;
use App\Services\AI\Providers\OpenRouterProvider;

test('the container resolves openrouter as the ai provider by config', function () {
    config(['ai.provider' => 'openrouter']);

    $provider = app(AiProviderInterface::class);

    expect($provider)->toBeInstanceOf(OpenRouterProvider::class);
    expect($provider->name())->toBe('openrouter');
});

test('an admin-selected model takes priority over the env fallback', function () {
    config(['ai.provider' => 'openrouter', 'ai.model' => 'openai/gpt-4o-mini']);
    app(AiSettingsService::class)->setSelectedModel('anthropic/claude-3.5-sonnet');

    $provider = app(AiProviderInterface::class);

    expect($provider->model())->toBe('anthropic/claude-3.5-sonnet');
});

test('the env model is used when no admin selection has been made', function () {
    config(['ai.provider' => 'openrouter', 'ai.model' => 'openai/gpt-4o-mini']);

    $provider = app(AiProviderInterface::class);

    expect($provider->model())->toBe('openai/gpt-4o-mini');
});
