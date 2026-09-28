<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AiProviderInterface;
use App\Services\AI\DTOs\AiProviderResponse;
use App\Services\AI\DTOs\RefundAnalysisContext;
use App\Services\AI\Exceptions\AiProviderException;
use App\Services\AI\RefundPromptBuilder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * OpenRouter exposes an OpenAI-compatible chat completions endpoint, but lets a
 * single API key call many different underlying models. Which model to use is
 * resolved by the caller (AiServiceProvider) from AiSettingsService, so an admin
 * can change it from the dashboard without touching the API key in .env.
 */
class OpenRouterProvider implements AiProviderInterface
{
    public function __construct(
        private readonly RefundPromptBuilder $promptBuilder,
        private readonly string $apiKey,
        private readonly string $model,
        private readonly int $timeoutSeconds,
        private readonly int $maxRetries,
    ) {}

    public function name(): string
    {
        return 'openrouter';
    }

    public function model(): string
    {
        return $this->model;
    }

    public function analyze(RefundAnalysisContext $context): AiProviderResponse
    {
        try {
            $response = $this->client()->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $this->model,
                'temperature' => 0,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $this->promptBuilder->systemPrompt()],
                    ['role' => 'user', 'content' => $this->promptBuilder->userContent($context)],
                ],
            ]);
        } catch (ConnectionException $e) {
            throw new AiProviderException('OpenRouter request timed out or the connection failed.', retryable: true, previous: $e);
        } catch (RequestException $e) {
            throw new AiProviderException(
                "OpenRouter request failed with status {$e->response->status()}.",
                retryable: $this->isRetryableStatus($e->response->status()),
                previous: $e,
            );
        }

        if ($response->failed()) {
            throw new AiProviderException(
                "OpenRouter request failed with status {$response->status()}.",
                retryable: $this->isRetryableStatus($response->status()),
            );
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw new AiProviderException('OpenRouter returned an empty response.', retryable: false);
        }

        return new AiProviderResponse(
            rawContent: $content,
            model: $response->json('model', $this->model),
            providerRequestId: $response->header('x-request-id') ?: null,
        );
    }

    private function isRetryableStatus(int $status): bool
    {
        return $status >= 500 || $status === 429;
    }

    private function client(): PendingRequest
    {
        return Http::withToken($this->apiKey)
            ->timeout($this->timeoutSeconds)
            ->retry($this->maxRetries + 1, 250, function (Throwable $exception) {
                if ($exception instanceof ConnectionException) {
                    return true;
                }

                if ($exception instanceof RequestException) {
                    return $this->isRetryableStatus($exception->response->status());
                }

                return false;
            }, throw: false);
    }
}
