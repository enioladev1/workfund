<?php

namespace App\Services\Refund;

use App\Exceptions\SafeApiException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Refund\DTOs\CreateOrderData;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Lets an admin create a synthetic order (and, optionally, a new customer) for
 * their own manual testing of the refund flow. Not part of the customer-facing
 * pipeline; this is purely a support/admin tool.
 */
class OrderCreationService
{
    public function create(CreateOrderData $data): Order
    {
        return DB::transaction(function () use ($data) {
            $customer = $data->customerId !== null
                ? Customer::query()->findOrFail($data->customerId)
                : Customer::create([
                    'name' => $data->newCustomerName,
                    'email' => $data->newCustomerEmail,
                    'phone' => $data->newCustomerPhone,
                ]);

            $orderNumber = $data->orderNumber ?? $this->generateUniqueOrderNumber();

            if (Order::query()->where('order_number', $orderNumber)->exists()) {
                throw new SafeApiException('That order number is already in use. Please choose another.', 422);
            }

            $itemTotals = array_map(
                fn (array $item) => Money::toMinorUnits($item['unit_price']) * $item['quantity'],
                $data->items,
            );

            $order = Order::create([
                'customer_id' => $customer->id,
                'order_number' => $orderNumber,
                'currency' => $data->currency,
                'total_minor' => array_sum($itemTotals),
                'is_final_sale' => $data->isFinalSale,
                'ordered_at' => $data->orderedAt,
                'delivered_at' => $data->deliveredAt,
            ]);

            foreach ($data->items as $index => $item) {
                $unitPriceMinor = Money::toMinorUnits($item['unit_price']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'sku' => $item['sku'],
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unit_price_minor' => $unitPriceMinor,
                    'total_price_minor' => $itemTotals[$index],
                    'is_final_sale' => $item['is_final_sale'],
                ]);
            }

            return $order->load(['customer', 'items']);
        });
    }

    private function generateUniqueOrderNumber(): string
    {
        do {
            $candidate = 'WF-'.Str::padLeft((string) random_int(0, 999999), 6, '0');
        } while (Order::query()->where('order_number', $candidate)->exists());

        return $candidate;
    }
}
