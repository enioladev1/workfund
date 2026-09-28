<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'string', 'uuid', Rule::exists('customers', 'id')],
            'new_customer_name' => ['nullable', 'string', 'max:255', 'required_without:customer_id'],
            'new_customer_email' => ['nullable', 'string', 'email', 'max:255', 'required_without:customer_id'],
            'new_customer_phone' => ['nullable', 'string', 'max:50'],

            'order_number' => ['nullable', 'string', 'max:50'],
            'currency' => ['required', 'string', 'size:3'],
            'is_final_sale' => ['required', 'boolean'],
            'ordered_at' => ['required', 'date', 'before_or_equal:today'],
            'delivered_at' => ['nullable', 'date', 'after_or_equal:ordered_at'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['required', 'string', 'max:50'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'items.*.unit_price' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'items.*.is_final_sale' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->filled('customer_id') && $this->filled('new_customer_email')) {
                $validator->errors()->add('customer_id', 'Choose either an existing customer or a new one, not both.');
            }
        });
    }
}
