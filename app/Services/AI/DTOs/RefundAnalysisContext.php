<?php

namespace App\Services\AI\DTOs;

/**
 * Everything the AI is given about a refund request. customerMessage is the only
 * untrusted field; everything else comes from our own database records.
 */
readonly class RefundAnalysisContext
{
    public function __construct(
        public string $orderNumber,
        public string $orderStatus,
        public bool $isFinalSale,
        public int $orderAgeDays,
        public string $itemName,
        public string $requestedReason,
        public string $requestedAmountFormatted,
        public string $orderTotalFormatted,
        public string $customerMessage,
    ) {}
}
