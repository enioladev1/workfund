<?php

namespace App\Http\Requests\Refund;

use App\Http\Requests\Concerns\RefundCustomerIdentityRules;
use Illuminate\Foundation\Http\FormRequest;

class LookupOrderRequest extends FormRequest
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
        return $this->identityRules();
    }
}
