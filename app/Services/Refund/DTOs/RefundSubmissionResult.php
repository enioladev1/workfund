<?php

namespace App\Services\Refund\DTOs;

use App\Models\RefundRequest;

readonly class RefundSubmissionResult
{
    public function __construct(
        public RefundRequest $refundRequest,
        public string $customerMessage,
        public bool $wasIdempotentReplay,
    ) {}
}
