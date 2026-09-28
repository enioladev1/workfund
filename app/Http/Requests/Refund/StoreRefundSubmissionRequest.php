<?php

namespace App\Http\Requests\Refund;

use App\Enums\RefundReason;
use App\Http\Requests\Concerns\RefundCustomerIdentityRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRefundSubmissionRequest extends FormRequest
{
    use RefundCustomerIdentityRules;

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
            ...$this->identityRules(),
            'order_item_id' => ['nullable', 'string', 'uuid'],
            'reason' => ['required', Rule::enum(RefundReason::class)],
            'customer_message' => ['required', 'string', 'min:5', 'max:2000'],
            'requested_amount_minor' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_message.min' => 'Please provide a few more details about your refund request.',
            'customer_message.max' => 'Your message is too long. Please keep it under 2000 characters.',
        ];
    }
}
