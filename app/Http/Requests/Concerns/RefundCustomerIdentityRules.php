<?php

namespace App\Http\Requests\Concerns;

/**
 * Shared between every customer-facing refund endpoint so the email/order_number
 * validation rules are defined exactly once.
 */
trait RefundCustomerIdentityRules
{
    /**
     * @return array<string, mixed>
     */
    protected function identityRules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'order_number' => ['required', 'string', 'max:50'],
        ];
    }
}
