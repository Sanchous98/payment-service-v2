<?php

namespace App\Http\Requests;

use App\Acquirers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use PaymentSystem\Laravel\Stripe;
use PaymentSystem\Laravel\Nuvei;

/**
 * @deprecated
 */
class StoreAccountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'description' => 'required|string|max:255',
            'supported_currencies' => 'array',
            'external_id' => 'sometimes|required|string|max:255|unique:accounts,external_id',
            'credentials.acquirer' => ['required', Rule::enum(Acquirers::class)],
//            'credentials.connexpay' => 'required_if:credentials.acquirer,connexpay|array:username,password,merchant_guid,device_guid',
//            'credentials.connexpay.username' => 'string',
//            'credentials.connexpay.password' => 'string',
//            'credentials.connexpay.merchant_guid' => 'uuid',
//            'credentials.connexpay.device_guid' => 'uuid',
            'credentials.stripe' => 'required_if:credentials.acquirer,stripe|array:api_key,webhook_signing_key',
            'credentials.stripe.api_key' => 'string',
            'credentials.stripe.webhook_signing_key' => 'string',
            'credentials.nuvei' => 'required_if:credentials.acquirer,nuvei|array:merchant_id,site_id,secret_key',
            'credentials.nuvei.merchant_id' => 'string',
            'credentials.nuvei.site_id' => 'string',
            'credentials.nuvei.secret_key' => 'string',
//            'credentials.paynet' => 'required_if:credentials.acquirer,paynet|array:merchant_code,sale_area_code,merchant_security_code,merchant_user,merchant_user_password',
//            'credentials.paynet.merchant_code' => 'numeric',
//            'credentials.paynet.sale_area_code' => 'string',
//            'credentials.paynet.merchant_security_code' => 'string',
//            'credentials.paynet.merchant_user' => 'numeric',
//            'credentials.paynet.merchant_user_password' => 'string',
        ];
    }

    public function credentials(): array
    {
        return $this->array('credentials' . $this->str('credentials.acquirer'));
    }

    public function account()
    {
        return match ($this->enum('credentials.acquirer', Acquirers::class)) {
            Acquirers::NUVEI => Nuvei\Models\Credentials::query()->create($this->input('credentials.nuvei')),
//            AcquirerEnum::CONNEXPAY => Connexpay\Models\Credentials::query()->create($this->input('credentials.connexpay')),
            Acquirers::STRIPE => Stripe\Models\Credentials::query()->create($this->input('credentials.stripe')),
//            AcquirerEnum::PAYNET => Paynet\Models\Credentials::query()->create($this->input('credentials.paynet')),
        };
    }
}
