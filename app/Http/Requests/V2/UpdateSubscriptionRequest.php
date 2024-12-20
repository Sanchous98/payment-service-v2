<?php

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'payment_method_id' => 'required|uuid|exists:payment_methods,id'
        ];
    }
}
