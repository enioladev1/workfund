<?php

namespace App\Services\AI\Exceptions;

use RuntimeException;

/**
 * Thrown for any transient or permanent AI provider failure (timeout, auth,
 * malformed response). Callers must never expose this message to customers.
 */
class AiProviderException extends RuntimeException
{
    public function __construct(string $message, public readonly bool $retryable = true, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
