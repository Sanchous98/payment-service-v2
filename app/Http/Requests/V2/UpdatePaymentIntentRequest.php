<?php

declare(strict_types=1);

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentIntentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'amount' => 'numeric|min:50|max:99999999'
        ];
    }
}
