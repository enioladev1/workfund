<?php

namespace App\Services\Refund\DTOs;

readonly class CreateOrderData
{
    /**
     * @param  list<array{name: string, sku: string, quantity: int, unit_price: string, is_final_sale: bool}>  $items
     */
    public function __construct(
        public ?string $customerId,
        public ?string $newCustomerName,
        public ?string $newCustomerEmail,
        public ?string $newCustomerPhone,
        public ?string $orderNumber,
        public string $currency,
        public bool $isFinalSale,
        public string $orderedAt,
        public ?string $deliveredAt,
        public array $items,
    ) {}
}
