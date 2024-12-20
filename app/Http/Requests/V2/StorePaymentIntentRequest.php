<?php

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Money\Currency;
use Money\Money;
use PaymentSystem\Enum\ECICodesEnum;
use PaymentSystem\Enum\SupportedVersionsEnum;
use PaymentSystem\Enum\ThreeDSStatusEnum;
use PaymentSystem\Laravel\Models\Account;
use PaymentSystem\Laravel\Uuid;
use PaymentSystem\ValueObjects\ThreeDSResult;

class StorePaymentIntentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'account_id' => 'required|uuid|exists:accounts,id',
            'token_id' => 'required_without:payment_method_id|uuid|exists:tokens,id',
            'payment_method_id' => 'required_without:token_id|uuid|exists:payment_methods,id',
            'amount' => 'required|numeric|min:50|max:99999999',
            'currency' => 'required|currency',
            'merchant_descriptor' => 'string|max:22',
            'description' => 'string|max:255',
            'three_ds' => [
                'array:status,authenticationValue,eci,dsTransactionId,acsTransactionId,version',
                'required_array_keys:status,authenticationValue,eci,dsTransactionId,acsTransactionId,version',
            ],
            'three_ds.status' => Rule::enum(ThreeDSStatusEnum::class),
            'three_ds.authenticationValue' => 'string',
            'three_ds.eci' => Rule::enum(ECICodesEnum::class),
            'three_ds.dsTransactionId' => 'uuid',
            'three_ds.acsTransactionId' => 'uuid',
            'three_ds.version' => Rule::enum(SupportedVersionsEnum::class),
        ];
    }

    public function account(): Account
    {
        return Account::query()->findOrFail($this->str('account_id'));
    }

    public function money(): Money
    {
        return new Money($this->str('amount'), new Currency($this->str('currency')));
    }

    public function threeDS(): ?ThreeDSResult
    {
        if (!$this->has('three_ds')) {
            return null;
        }

        return new ThreeDSResult(
            $this->enum('three_ds.status', ThreeDSStatusEnum::class),
            $this->str('three_ds.authenticationValue'),
            $this->enum('three_ds.eci', ECICodesEnum::class),
            Uuid::fromString($this->str('three_ds.dsTransactionId')),
            Uuid::fromString($this->str('three_ds.acsTransactionId')),
            '',
            $this->enum('three_ds.version', SupportedVersionsEnum::class),
        );
    }

    public function tenderId(): Uuid
    {
        return Uuid::fromString($this->str('token_id', $this->str('payment_method_id')));
    }
}
