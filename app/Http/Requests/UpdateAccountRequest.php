<?php

namespace App\Http\Requests;

use App\Acquirers;
use Illuminate\Foundation\Http\FormRequest;
use PaymentSystem\Laravel\Models\Account;

/**
 * @deprecated
 * @property Account $account
 */
class UpdateAccountRequest extends FormRequest
{
    public function prepareForValidation(): void
    {
        if (!$this->isMethod('PATCH')) {
            return;
        }

        $oldInput = $this->input();

        $this->merge([
            'description' => $this->account->description,
            'credentials' => [
                ...match($this->account->credentials_type) {
//                    Acquirers::CONNEXPAY => ['connexpay' => $this->account->credentials->only($this->account->credentials->getFillable())],
                    Acquirers::NUVEI => ['nuvei' => $this->account->credentials->only($this->account->credentials->getFillable())],
                    Acquirers::STRIPE => ['stripe' => $this->account->credentials->only($this->account->credentials->getFillable())],
                }
            ],
            ...(isset($this->account->external_id)) ?['external_id' => $this->account->external_id] : [],
        ]);

        $this->merge($oldInput);
    }

    public function rules(): array
    {
        return [
            'description' => 'required|string|max:255',
            'external_id' => 'sometimes|required|string|max:255',
            'supported_currencies' => 'array',
            'credentials' => "required|array:{$this->account->credentials_type}",
            'credentials.connexpay' => 'array:username,password,merchant_guid,device_guid',
            'credentials.connexpay.username' => 'string',
            'credentials.connexpay.password' => 'string',
            'credentials.connexpay.merchant_guid' => 'uuid',
            'credentials.connexpay.device_guid' => 'uuid',
            'credentials.stripe' => 'array:api_key,webhook_signing_key',
            'credentials.stripe.api_key' => 'string',
            'credentials.stripe.webhook_signing_key' => 'string',
            'credentials.nuvei' => 'array:merchant_id,site_id,secret_key',
            'credentials.nuvei.merchant_id' => 'string',
            'credentials.nuvei.site_id' => 'string',
            'credentials.nuvei.secret_key' => 'string',
        ];
    }

    public function credentials(): array
    {
        return $this->array('credentials' . $this->account->credentials_type);
    }
}
