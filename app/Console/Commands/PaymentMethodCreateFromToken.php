<?php

namespace App\Console\Commands;

use App\Models\Token;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Str;
use PaymentSystem\Laravel\Jobs\CreateTokenPaymentMethodJob;
use PaymentSystem\Laravel\Models\Account;
use PaymentSystem\Laravel\Uuid;
use PaymentSystem\Repositories\TokenRepositoryInterface;
use PaymentSystem\TokenAggregateRoot;
use PaymentSystem\ValueObjects\BillingAddress;
use PaymentSystem\ValueObjects\Country;
use PaymentSystem\ValueObjects\Email;
use PaymentSystem\ValueObjects\PhoneNumber;
use PaymentSystem\ValueObjects\State;
use Symfony\Component\Intl\Countries;

use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class PaymentMethodCreateFromToken extends Command implements PromptsForMissingInput
{
    private readonly Uuid $uuid;
    
    protected $signature = 'payments:payment-method:create-from-token
                            {token : Token ID}
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
    protected $description = 'Create a payment method from previously created token';

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'token' => fn() => select(
                label: 'Token ID',
                options: Token::query()->where(['used' => false])->pluck('id'),
                validate: 'uuid',
            ),
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
                validate: 'country',
            ),
            'state' => fn() => match ($this->argument('country')) {
                'AU', 'IN', 'GB', 'NZ', 'CA', 'US' => select(
                    label: 'State',
                    options: collect(State::all(new Country($this->argument('country'))))
                        ->flatMap(fn(State $state) => [(string)$state => Str::title($state->getName())]),
                    validate: "string|max:255|state:{$this->argument('country')}",
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

    public function __construct(private readonly TokenRepositoryInterface $repository)
    {
        parent::__construct();
        $this->uuid = new Uuid(Str::orderedUuid());
    }

    public function __invoke(Dispatcher $dispatcher): void
    {
        $dispatcher->dispatch(
            new CreateTokenPaymentMethodJob(
                $this->uuid,
                $this->getBillingAddress(),
                $this->getToken(),
                ...Account::all(),
            )
        );

        info("Payment method $this->uuid was created");
    }

    public function getBillingAddress(): BillingAddress
    {
        return new BillingAddress(
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
        );
    }

    public function getToken(): TokenAggregateRoot
    {
        return $this->repository->retrieve(Uuid::fromString($this->argument('token')));
    }
}
