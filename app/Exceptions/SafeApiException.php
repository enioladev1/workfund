<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Any exception of this type carries a message that is already safe to show a
 * customer: simple English, no internal detail. The global exception handler
 * (bootstrap/app.php) renders it as-is; every other exception gets a generic
 * fallback message instead so internals never leak.
 */
class SafeApiException extends RuntimeException
{
    public function __construct(string $safeMessage, public readonly int $statusCode = 422)
    {
        parent::__construct($safeMessage);
    }
}
