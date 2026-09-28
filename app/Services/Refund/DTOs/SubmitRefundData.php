<?php

namespace App\Services\Refund\DTOs;

use App\Enums\RefundReason;

readonly class SubmitRefundData
{
    public function __construct(
        public string $email,
        public string $orderNumber,
        public ?string $orderItemId,
        public RefundReason $reason,
        public string $customerMessage,
        public ?int $requestedAmountMinor,
        public ?string $idempotencyKey,
    ) {}
}
