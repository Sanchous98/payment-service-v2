<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreBillingAddress extends FormRequest
{
    public function prepareForValidation(): void
    {
        $this->getInputSource()->set('country', Str::upper($this->input('country')));
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address_line' => 'required|string|max:255',
            'address_line_extra' => 'string|max:255',
            'city' => 'required|string|max:255',
            'state' => [
                'required_if:country,US', //,AU,IN,GB,NZ,CA',
                'string',
                'max:255',
                Rule::when(
                    in_array((string)$this->str('country'), [/*'AU', 'IN', 'GB', 'NZ', 'CA',*/ 'US'], true),
                    "state:{$this->str('country')}",
                ),
            ],
            'country' => 'required|country',
            'postal_code' => 'required|string|max:128',
            'phone' => 'required|phone',
            'email' => 'required|email:filter',
        ];
    }
}
