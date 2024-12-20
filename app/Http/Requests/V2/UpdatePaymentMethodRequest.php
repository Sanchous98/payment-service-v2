<?php

namespace App\Http\Requests\V2;

use App\Models\BillingAddress;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentMethodRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'billing_address_id' => 'required|uuid|exists:billing_address,id',
        ];
    }

    public function billingAddress(): BillingAddress
    {
        return BillingAddress::query()->find($this->string('billing_address_id'));
    }
}
