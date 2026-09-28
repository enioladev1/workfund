<?php

namespace App\Services\AI\DTOs;

/**
 * Raw, unvalidated text returned by a provider. Must go through
 * AiResponseValidator before any of it is trusted.
 */
readonly class AiProviderResponse
{
    public function __construct(
        public string $rawContent,
        public string $model,
        public ?string $providerRequestId,
    ) {}
}
