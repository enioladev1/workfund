<?php

use App\Services\AI\AiSettingsService;

test('returns null when no model has been selected yet', function () {
    $service = app(AiSettingsService::class);

    expect($service->getSelectedModel())->toBeNull();
});

test('persists and returns the selected model', function () {
    $service = app(AiSettingsService::class);

    $service->setSelectedModel('openai/gpt-4o-mini');

    expect($service->getSelectedModel())->toBe('openai/gpt-4o-mini');
    $this->assertDatabaseHas('ai_settings', ['key' => 'selected_model']);
});

test('updating the selected model overwrites the previous value', function () {
    $service = app(AiSettingsService::class);

    $service->setSelectedModel('openai/gpt-4o-mini');
    $service->setSelectedModel('anthropic/claude-3.5-sonnet');

    expect($service->getSelectedModel())->toBe('anthropic/claude-3.5-sonnet');
    $this->assertDatabaseCount('ai_settings', 1);
});
