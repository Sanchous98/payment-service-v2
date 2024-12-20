<?php

declare(strict_types=1);

namespace App\Http\Requests\V2;

use App\Contracts\EncryptAwareInterface;
use App\Models\BillingAddress;
use Illuminate\Foundation\Http\FormRequest;
use LVR\CreditCard\CardCvc;
use LVR\CreditCard\CardExpirationMonth;
use LVR\CreditCard\CardExpirationYear;
use LVR\CreditCard\CardNumber;
use PaymentSystem\Contracts\EncryptInterface;
use PaymentSystem\Contracts\SourceInterface;
use PaymentSystem\ValueObjects\CreditCard;

class StorePaymentMethodRequest extends FormRequest implements EncryptAwareInterface
{
    private EncryptInterface $encrypt;

    public function setEncrypt(EncryptInterface $encrypt): void
    {
        $this->encrypt = $encrypt;
    }

    public function rules(): array
    {
        return [
            'token_id' => 'required_without:source|uuid|exists:tokens,id',
            'billing_address_id' => 'required_without:token_id|uuid|exists:billing_addresses,id',
            'source' => 'required_without:token_id|array:type,card|required_array_keys:type',
            'source.type' => 'string|in:card', // todo: other payment methods
            'source.card' => 'required_if:type,card|array:number,expiration_month,expiration_year,holder,cvc|required_array_keys:number,expiration_month,expiration_year,holder',
            'source.card.expiration_month' => new CardExpirationMonth($this->integer('source.card.expiration_year')),
            'source.card.expiration_year' => new CardExpirationYear($this->integer('source.card.expiration_month')),
            'source.card.holder' => 'string|max:70',
            'source.card.number' => new CardNumber(),
            'source.card.cvc' => new CardCvc($this->string('source.card.number')),
        ];
    }

    public function passedValidation(): void
    {
        if (!$this->has('source')) {
            return;
        }

        $this->merge([
            'source' => [
                ...$this->input('source'),
                'card' => [
                    ...$this->input('card'),
                    'number' => CreditCard\Number::fromNumber((string)$this->str('source.card.number'), $this->encrypt),
                    'cvc' => $this->has('cvc') ? CreditCard\Cvc::fromCvc((string)$this->str('source.card.cvc'), $this->encrypt) : new CreditCard\Cvc(),
                ],
            ]
        ]);
    }

    public function billingAddress(): BillingAddress
    {
        return BillingAddress::query()->findOrFail($this->str('billing_address_id'));
    }

    public function source(): SourceInterface
    {
        return match ((string)$this->string('source.type')) {
            CreditCard::TYPE => new CreditCard(
                $this->input('source.card.number'),
                CreditCard\Expiration::fromMonthAndYear($this->integer('source.card.expiration_month'), $this->integer('source.card.expiration_year')),
                new CreditCard\Holder((string)$this->str('source.card.holder')),
                $this->input('source.card.cvc'),
            ),
            default => throw new \RuntimeException('unsupported type')
        };
    }
}
