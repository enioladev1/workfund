<?php

namespace Tests\Fixtures;

use App\Services\AI\Contracts\AiProviderInterface;
use App\Services\AI\DTOs\AiProviderResponse;
use App\Services\AI\DTOs\RefundAnalysisContext;
use App\Services\AI\Exceptions\AiProviderException;

class FakeAiProvider implements AiProviderInterface
{
    private ?string $rawContent = null;

    private ?AiProviderException $exceptionToThrow = null;

    public static function returning(array $structuredOutput): self
    {
        $provider = new self;
        $provider->rawContent = json_encode($structuredOutput);

        return $provider;
    }

    public static function returningRaw(string $rawContent): self
    {
        $provider = new self;
        $provider->rawContent = $rawContent;

        return $provider;
    }

    public static function throwing(AiProviderException $exception): self
    {
        $provider = new self;
        $provider->exceptionToThrow = $exception;

        return $provider;
    }

    public function analyze(RefundAnalysisContext $context): AiProviderResponse
    {
        if ($this->exceptionToThrow !== null) {
            throw $this->exceptionToThrow;
        }

        return new AiProviderResponse(
            rawContent: $this->rawContent ?? '{}',
            model: 'fake-model',
            providerRequestId: 'fake-request-id',
        );
    }

    public function name(): string
    {
        return 'fake';
    }

    public function model(): string
    {
        return 'fake-model';
    }
}
