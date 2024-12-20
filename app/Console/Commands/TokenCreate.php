<?php

namespace App\Console\Commands;

use DateTimeImmutable;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Str;
use LVR\CreditCard\CardCvc;
use LVR\CreditCard\CardExpirationDate;
use LVR\CreditCard\CardNumber;
use PaymentSystem\Contracts\EncryptInterface;
use PaymentSystem\Laravel\Jobs\CreateTokenJob;
use PaymentSystem\Laravel\Models\Account;
use PaymentSystem\Laravel\Uuid;
use PaymentSystem\ValueObjects\CreditCard;

use function Laravel\Prompts\info;
use function Laravel\Prompts\text;

class TokenCreate extends Command implements PromptsForMissingInput
{
    protected $signature = 'payments:token:create
                            {number : Credit card number}
                            {expiration : Expiration date in MMYY format}
                            {holder : Card holder name}
                            {cvc : Card verification code}';
    protected $description = 'Create a token for credit card for a single usage in the system';
    private readonly Uuid $uuid;

    public function __construct(private readonly EncryptInterface $encrypt)
    {
        parent::__construct();

        $this->uuid = new Uuid(Str::orderedUuid());
    }

    public function __invoke(Dispatcher $dispatcher): void
    {
        $dispatcher->dispatch(
            new CreateTokenJob(
                $this->uuid,
                $this->getCard(),
                ...Account::all(),
            )
        );

        info("Token $this->uuid created successfully");
    }

    public function getCard(): CreditCard
    {
        $cvc = $this->argument('cvc');

        return new CreditCard(
            CreditCard\Number::fromNumber($this->argument('number'), $this->encrypt),
            new CreditCard\Expiration(DateTimeImmutable::createFromFormat('ny', $this->argument('expiration'))),
            new CreditCard\Holder($this->argument('holder')),
            $cvc !== null ? CreditCard\Cvc::fromCvc($cvc, $this->encrypt) : new CreditCard\Cvc(),
        );
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'number' => fn() => text(
                label: "What's the number of card you would like to tokenize?",
                required: true,
                validate: new CardNumber(),
            ),
            'expiration' => fn() => text(
                label: "What's the expiration date of the card?",
                placeholder: 'MMYY',
                required: true,
                validate: new CardExpirationDate('ny'),
            ),
            'holder' => fn() => text(
                label: "What's your card holder name?",
                placeholder: 'firstname lastname',
                required: true,
                validate: 'string|max:70',
                transform: strtoupper(...),
            ),
            'cvc' => fn() => text(
                label: "What's your card verification code? (Not required, but it's preferred)",
                placeholder: '1234',
                validate: new CardCvc($this->argument('number')),
            ),
        ];
    }
}
