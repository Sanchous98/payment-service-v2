<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BillingAddress;
use DateTimeImmutable;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LVR\CreditCard\CardCvc;
use LVR\CreditCard\CardExpirationDate;
use LVR\CreditCard\CardNumber;
use PaymentSystem\Contracts\EncryptInterface;
use PaymentSystem\Contracts\SourceInterface;
use PaymentSystem\Laravel\Jobs\CreateBillingAddressJob;
use PaymentSystem\Laravel\Jobs\CreatePaymentMethodJob;
use PaymentSystem\Laravel\Models\Account;
use PaymentSystem\Laravel\Uuid;
use PaymentSystem\ValueObjects\Country;
use PaymentSystem\ValueObjects\CreditCard;
use PaymentSystem\ValueObjects\Email;
use PaymentSystem\ValueObjects\PhoneNumber;
use PaymentSystem\ValueObjects\State;
use Symfony\Component\Intl\Countries;

use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class PaymentMethodCreate extends Command implements PromptsForMissingInput
{
    protected $signature = 'payments:payment-method:create
                            {type : Source type}
                            {number : Credit card number}
                            {expiration : Expiration date in MMYY format}
                            {holder : Card holder name}
                            {cvc : Card verification code}
                            {firstName : First Name}
                            {lastName : Last Name}
                            {addressLine : Address line}
                            {addressLineExtra : Address line extra}
                            {country : Country code}
                            {state : State}
                            {city : City}
                            {postalCode : Postal code}
                            {phone : Phone number}
                            {email : Email}';
    protected $description = 'Create a payment method from source data';
    private readonly Uuid $uuid;

    public function __construct(private readonly EncryptInterface $encrypt)
    {
        parent::__construct();

        $this->uuid = new Uuid(Str::orderedUuid());
    }

    public function __invoke(Dispatcher $dispatcher): void
    {
        $dispatcher->dispatchSync(new CreateBillingAddressJob(
            $id = new Uuid(Str::uuid7()),
            $this->argument('firstName'),
            $this->argument('lastName'),
            $this->argument('city'),
            new Country($this->argument('country')),
            $this->argument('postalCode'),
            new Email($this->argument('email')),
            new PhoneNumber($this->argument('phone')),
            $this->argument('addressLine'),
            $this->argument('addressLineExtra'),
            $this->argument('state') ? new State($this->argument('state')) : null,
        ));
        $dispatcher->dispatch(
            new CreatePaymentMethodJob(
                $this->uuid,
                BillingAddress::query()->findOrFail($id)->toEntity(),
                $this->getSource(),
                ...Account::all(),
            )
        );

        info("Payment method $this->uuid was created");
    }

    public function getSource(): SourceInterface
    {
        $cvc = $this->argument('cvc');

        return match ($this->argument('type')) {
            CreditCard::TYPE => new CreditCard(
                CreditCard\Number::fromNumber($this->argument('number'), $this->encrypt),
                new CreditCard\Expiration(DateTimeImmutable::createFromFormat('ny', $this->argument('expiration'))),
                new CreditCard\Holder($this->argument('holder')),
                $cvc !== null ? CreditCard\Cvc::fromCvc($cvc, $this->encrypt) : new CreditCard\Cvc(),
            ),
        };
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'type' => fn() => select(
                label: 'Source type',
                options: [
                    CreditCard::TYPE => 'Credit card',
                ],
                validate: Rule::in([CreditCard::TYPE])
            ),
            ...match ($this->argument('type')) {
                CreditCard::TYPE => [
                    'number' => fn() => text(
                        label: "What's the number of card you would like to use for payment method?",
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
                ],
                default => [],
            },
            'firstName' => fn() => text(
                label: 'First Name',
                required: true,
                validate: 'string|max:255',
            ),
            'lastName' => fn() => text(
                label: 'Last Name',
                required: true,
                validate: 'string|max:255',
            ),
            'addressLine' => fn() => text(
                label: 'Address line',
                required: true,
                validate: 'string|max:255',
            ),
            'addressLineExtra' => fn() => text(
                label: 'Address line extra (Optional)',
                validate: 'string|max:255',
            ),
            'country' => fn() => select(
                label: 'Country',
                options: collect(Countries::getNames()),
                validate: 'required|country',
            ),
            'state' => fn() => match ($this->argument('country')) {
                'AU', 'IN', 'GB', 'NZ', 'CA', 'US' => select(
                    label: 'State',
                    options: collect(State::all(new Country($this->argument('country'))))
                        ->flatMap(fn(State $state) => [(string)$state => $state->getName()]),
                    validate: "string|max:3|state:{$this->argument('country')}",
                ),
                default => text('State (Optional)', validate: 'string|max:255'),
            },
            'city' => fn() => text(
                label: 'City',
                required: true,
                validate: 'string|max:255',
            ),
            'postalCode' => fn() => text(
                label: 'Postal code',
                required: true,
                validate: 'string|max:128'
            ),
            'phone' => fn() => text(
                label: 'Phone number',
                required: true,
                validate: 'phone',
            ),
            'email' => fn() => text(
                label: 'Email address',
                required: true,
                validate: 'email:filter',
            ),
        ];
    }
}
