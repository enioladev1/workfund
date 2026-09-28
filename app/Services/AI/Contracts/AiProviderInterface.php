<?php

namespace App\Services\AI\Contracts;

use App\Services\AI\DTOs\AiProviderResponse;
use App\Services\AI\DTOs\RefundAnalysisContext;
use App\Services\AI\Exceptions\AiProviderException;

interface AiProviderInterface
{
    /**
     * @throws AiProviderException on any transport, auth, or provider-side error.
     */
    public function analyze(RefundAnalysisContext $context): AiProviderResponse;

    public function name(): string;

    public function model(): string;
}
