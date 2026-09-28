<?php

namespace App\Http\Requests\Admin;

use App\Enums\RefundReason;
use App\Enums\RefundStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RefundIndexRequest extends FormRequest
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
            'status' => ['nullable', Rule::enum(RefundStatus::class)],
            'reason' => ['nullable', Rule::enum(RefundReason::class)],
            'customer_email' => ['nullable', 'string', 'max:255'],
            'order_number' => ['nullable', 'string', 'max:50'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return $this->only(['status', 'reason', 'customer_email', 'order_number', 'date_from', 'date_to']);
    }
}
