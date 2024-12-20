<?php

namespace App\Http\Requests\V2;

use App\Contracts\EncryptAwareInterface;
use Illuminate\Foundation\Http\FormRequest;
use LVR\CreditCard\CardCvc;
use LVR\CreditCard\CardExpirationMonth;
use LVR\CreditCard\CardExpirationYear;
use LVR\CreditCard\CardNumber;
use PaymentSystem\Contracts\EncryptInterface;
use PaymentSystem\Contracts\TokenizedSourceInterface;
use PaymentSystem\ValueObjects\CreditCard;

class StoreTokenRequest extends FormRequest implements EncryptAwareInterface
{
    private EncryptInterface $encrypt;

    public function rules(): array
    {
        return [
            'type' => 'required|string|in:card', // todo: other tokenizable sources
            'card' => 'required_if:type,card|array:number,expiration_month,expiration_year,holder,cvc|required_array_keys:number,expiration_month,expiration_year,holder',
            'card.expiration_month' => new CardExpirationMonth($this->integer('card.expiration_year')),
            'card.expiration_year' => new CardExpirationYear($this->integer('card.expiration_month')),
            'card.holder' => 'string|max:70',
            'card.number' => new CardNumber(),
            'card.cvc' => new CardCvc($this->string('card.number')),
        ];
    }

    public function passedValidation(): void
    {
        $this->merge([
            'card' => [
                ...$this->input('card'),
                'number' => CreditCard\Number::fromNumber($this->str('card.number'), $this->encrypt),
                'cvc' => $this->has('cvc') ? CreditCard\Cvc::fromCvc($this->string('card.cvc'), $this->encrypt) : new CreditCard\Cvc(),
            ],
        ]);
    }

    public function source(): TokenizedSourceInterface
    {
        return match ((string)$this->string('type')) {
            CreditCard::TYPE => new CreditCard(
                $this->input('card.number'),
                CreditCard\Expiration::fromMonthAndYear($this->integer('card.expiration_month'), $this->integer('card.expiration_year')),
                new CreditCard\Holder($this->string('card.holder')),
                $this->input('card.cvc'),
            ),
            default => throw new \RuntimeException('Unsupported type')
        };
    }

    public function setEncrypt(EncryptInterface $encrypt): void
    {
        $this->encrypt = $encrypt;
    }
}
