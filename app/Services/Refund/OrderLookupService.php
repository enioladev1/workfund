<?php

namespace App\Services\Refund;

use App\Exceptions\SafeApiException;
use App\Models\Customer;
use App\Models\Order;

/**
 * Order lookups are always scoped to the customer resolved from the given
 * email; an order_number is never trusted on its own. This is the IDOR
 * protection for the customer-facing side of the app.
 */
class OrderLookupService
{
    public function findForCustomer(string $email, string $orderNumber): Order
    {
        // Postgres string equality is case-sensitive; email/order-number matching
        // should not be, since customers routinely retype these with different casing.
        $customer = Customer::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($email)])->first();

        if ($customer === null) {
            throw new SafeApiException("We couldn't find a customer with that email. Please check the email address and try again.", 404);
        }

        $order = Order::query()
            ->with('items')
            ->whereRaw('LOWER(order_number) = ?', [mb_strtolower($orderNumber)])
            ->where('customer_id', $customer->id)
            ->first();

        if ($order === null) {
            throw new SafeApiException("We couldn't find that order. Please check the order number and try again.", 404);
        }

        return $order;
    }
}
