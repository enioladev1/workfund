<?php

use App\Models\User;
use App\Services\AI\AiSettingsService;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

function fakeOpenRouterCatalog(): void
{
    Http::fake([
        'openrouter.ai/api/v1/models' => Http::response([
            'data' => [
                [
                    'id' => 'openai/gpt-4o-mini',
                    'name' => 'OpenAI: GPT-4o mini',
                    'context_length' => 128000,
                    'architecture' => ['output_modalities' => ['text']],
                    'pricing' => ['prompt' => '0.00000015', 'completion' => '0.0000006'],
                ],
                [
                    'id' => 'anthropic/claude-3.5-sonnet',
                    'name' => 'Anthropic: Claude 3.5 Sonnet',
                    'context_length' => 200000,
                    'architecture' => ['output_modalities' => ['text']],
                    'pricing' => ['prompt' => '0.000003', 'completion' => '0.000015'],
                ],
                [
                    'id' => 'some/image-only-model',
                    'name' => 'Image only model',
                    'context_length' => 4096,
                    'architecture' => ['output_modalities' => ['image']],
                    'pricing' => ['prompt' => '0', 'completion' => '0'],
                ],
            ],
        ], 200),
    ]);
}

test('guests cannot access the admin settings page', function () {
    $this->get('/admin/settings')->assertRedirect('/login');
});

test('the settings page lists text-output models from the openrouter catalog', function () {
    config(['ai.provider' => 'openrouter']);
    fakeOpenRouterCatalog();

    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->get('/admin/settings');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/settings')
        ->where('provider', 'openrouter')
        ->has('models', 2)
        ->where('models.0.id', 'anthropic/claude-3.5-sonnet'),
    );
});

test('an admin can select a model from the catalog', function () {
    config(['ai.provider' => 'openrouter']);
    fakeOpenRouterCatalog();

    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->put('/admin/settings', [
        'model' => 'anthropic/claude-3.5-sonnet',
    ]);

    $response->assertRedirect();
    expect(app(AiSettingsService::class)->getSelectedModel())->toBe('anthropic/claude-3.5-sonnet');
});

test('a model not present in the catalog is rejected', function () {
    config(['ai.provider' => 'openrouter']);
    fakeOpenRouterCatalog();

    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->put('/admin/settings', [
        'model' => 'made-up/not-a-real-model',
    ]);

    $response->assertSessionHasErrors('model');
    expect(app(AiSettingsService::class)->getSelectedModel())->toBeNull();
});
