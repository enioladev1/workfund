<?php

namespace App\Services\AI;

use App\Models\AiInteraction;
use App\Models\RefundRequest;
use App\Services\AI\Contracts\AiProviderInterface;
use App\Services\AI\DTOs\RefundAiAnalysis;
use App\Services\AI\DTOs\RefundAnalysisContext;
use App\Services\AI\Exceptions\AiProviderException;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * The AI is an assistant only: it classifies and explains, but every field it
 * returns is validated before use, and nothing here can produce a refund
 * decision on its own. See RefundDecisionService for how AI input is weighed
 * against the (authoritative) policy result.
 */
class AiService
{
    public function __construct(
        private readonly AiProviderInterface $provider,
        private readonly AiResponseValidator $validator,
        private readonly PromptInjectionGuard $injectionGuard,
    ) {}

    /**
     * @return array{analysis: ?RefundAiAnalysis, injection_detected: bool}
     */
    public function analyze(RefundRequest $refundRequest, RefundAnalysisContext $context): array
    {
        $injectionDetected = $this->injectionGuard->detect($context->customerMessage);

        $startedAt = microtime(true);

        try {
            $response = $this->provider->analyze($context);
            $latencyMs = (int) ((microtime(true) - $startedAt) * 1000);

            $analysis = $this->validator->validate($response->rawContent);

            if ($analysis === null) {
                $this->recordInteraction($refundRequest, 'failed', null, 'AI response failed structured-output validation.', $latencyMs, $response->providerRequestId);

                return ['analysis' => null, 'injection_detected' => $injectionDetected];
            }

            $this->recordInteraction($refundRequest, 'success', $analysis->toArray(), null, $latencyMs, $response->providerRequestId);

            return ['analysis' => $analysis, 'injection_detected' => $injectionDetected];
        } catch (AiProviderException $e) {
            $latencyMs = (int) ((microtime(true) - $startedAt) * 1000);

            Log::warning('AI provider failed during refund analysis.', [
                'refund_request_id' => $refundRequest->id,
                'retryable' => $e->retryable,
                'error' => $e->getMessage(),
            ]);

            $this->recordInteraction($refundRequest, 'failed', null, $e->getMessage(), $latencyMs, null);

            return ['analysis' => null, 'injection_detected' => $injectionDetected];
        } catch (Throwable $e) {
            Log::error('Unexpected error during AI refund analysis.', [
                'refund_request_id' => $refundRequest->id,
                'error' => $e->getMessage(),
            ]);

            $this->recordInteraction($refundRequest, 'failed', null, 'Unexpected error.', null, null);

            return ['analysis' => null, 'injection_detected' => $injectionDetected];
        }
    }

    /**
     * @param  array<string, mixed>|null  $structuredOutput
     */
    private function recordInteraction(
        RefundRequest $refundRequest,
        string $status,
        ?array $structuredOutput,
        ?string $errorMessage,
        ?int $latencyMs,
        ?string $providerRequestId,
    ): void {
        AiInteraction::create([
            'refund_request_id' => $refundRequest->id,
            'provider' => $this->provider->name(),
            'model' => $this->provider->model(),
            'provider_request_id' => $providerRequestId,
            'status' => $status,
            'structured_output' => $structuredOutput,
            'error_message' => $errorMessage,
            'latency_ms' => $latencyMs,
        ]);
    }
}
