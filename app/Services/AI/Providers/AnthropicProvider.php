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

class AnthropicProvider implements AiProviderInterface
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
        return 'anthropic';
    }

    public function model(): string
    {
        return $this->model;
    }

    public function analyze(RefundAnalysisContext $context): AiProviderResponse
    {
        try {
            $response = $this->client()->post('https://api.anthropic.com/v1/messages', [
                'model' => $this->model,
                'max_tokens' => 500,
                'temperature' => 0,
                'system' => $this->promptBuilder->systemPrompt(),
                'messages' => [
                    ['role' => 'user', 'content' => $this->promptBuilder->userContent($context)],
                ],
            ]);
        } catch (ConnectionException $e) {
            throw new AiProviderException('Anthropic request timed out or the connection failed.', retryable: true, previous: $e);
        } catch (RequestException $e) {
            throw new AiProviderException(
                "Anthropic request failed with status {$e->response->status()}.",
                retryable: $this->isRetryableStatus($e->response->status()),
                previous: $e,
            );
        }

        if ($response->failed()) {
            throw new AiProviderException(
                "Anthropic request failed with status {$response->status()}.",
                retryable: $this->isRetryableStatus($response->status()),
            );
        }

        $content = $response->json('content.0.text');

        if (! is_string($content) || $content === '') {
            throw new AiProviderException('Anthropic returned an empty response.', retryable: false);
        }

        return new AiProviderResponse(
            rawContent: $content,
            model: $response->json('model', $this->model),
            providerRequestId: $response->header('request-id') ?: null,
        );
    }

    private function isRetryableStatus(int $status): bool
    {
        return $status >= 500 || $status === 429;
    }

    private function client(): PendingRequest
    {
        return Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
        ])
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
