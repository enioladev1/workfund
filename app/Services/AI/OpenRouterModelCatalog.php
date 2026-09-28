<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Lists models available through OpenRouter, so the admin settings page can offer
 * a real, current list instead of a hardcoded one. Cached because this list
 * changes rarely and OpenRouter's own rate limits are worth respecting.
 */
class OpenRouterModelCatalog
{
    private const CACHE_KEY = 'ai:openrouter_models';

    private const CACHE_TTL_SECONDS = 3600;

    public function __construct(private readonly string $apiKey) {}

    /**
     * @return list<array{id: string, name: string, context_length: int|null, prompt_price: string|null, completion_price: string|null}>
     */
    public function models(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            try {
                $response = Http::withToken($this->apiKey)
                    ->timeout(10)
                    ->get('https://openrouter.ai/api/v1/models');

                if ($response->failed()) {
                    return [];
                }

                /** @var list<array<string, mixed>> $rawModels */
                $rawModels = $response->json('data', []);

                $models = [];

                foreach ($rawModels as $model) {
                    if (! in_array('text', $model['architecture']['output_modalities'] ?? [], true)) {
                        continue;
                    }

                    $models[] = [
                        'id' => (string) $model['id'],
                        'name' => (string) ($model['name'] ?? $model['id']),
                        'context_length' => isset($model['context_length']) ? (int) $model['context_length'] : null,
                        'prompt_price' => isset($model['pricing']['prompt']) ? (string) $model['pricing']['prompt'] : null,
                        'completion_price' => isset($model['pricing']['completion']) ? (string) $model['pricing']['completion'] : null,
                    ];
                }

                usort($models, fn (array $a, array $b) => $a['name'] <=> $b['name']);

                return $models;
            } catch (Throwable $e) {
                Log::warning('Failed to fetch OpenRouter model catalog.', ['error' => $e->getMessage()]);

                return [];
            }
        });
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
