<?php

namespace App\Providers;

use App\Services\AI\AiSettingsService;
use App\Services\AI\Contracts\AiProviderInterface;
use App\Services\AI\OpenRouterModelCatalog;
use App\Services\AI\Providers\AnthropicProvider;
use App\Services\AI\Providers\OpenAiProvider;
use App\Services\AI\Providers\OpenRouterProvider;
use App\Services\AI\RefundPromptBuilder;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

/**
 * Binds AiProviderInterface to a concrete provider based on config('ai.provider').
 * Swapping providers is a config change, never a code change in callers.
 */
class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OpenRouterModelCatalog::class, fn () => new OpenRouterModelCatalog((string) config('ai.api_key')));

        $this->app->bind(AiProviderInterface::class, function () {
            $provider = config('ai.provider');
            $promptBuilder = $this->app->make(RefundPromptBuilder::class);
            $apiKey = (string) config('ai.api_key');
            $timeout = (int) config('ai.timeout_seconds');
            $maxRetries = (int) config('ai.max_retries');

            return match ($provider) {
                'openai' => new OpenAiProvider($promptBuilder, $apiKey, (string) config('ai.model'), $timeout, $maxRetries),
                'anthropic' => new AnthropicProvider($promptBuilder, $apiKey, (string) config('ai.model'), $timeout, $maxRetries),
                // The model is admin-editable for OpenRouter (see AiSettingsService);
                // every other provider uses the fixed AI_MODEL from .env.
                'openrouter' => new OpenRouterProvider(
                    $promptBuilder,
                    $apiKey,
                    $this->app->make(AiSettingsService::class)->getSelectedModel() ?? (string) config('ai.model'),
                    $timeout,
                    $maxRetries,
                ),
                default => throw new RuntimeException("Unsupported AI_PROVIDER: {$provider}"),
            };
        });
    }
}
