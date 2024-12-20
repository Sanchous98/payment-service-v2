<?php

declare(strict_types=1);

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
                            {token : Token ID}';
    protected $description = 'Create a payment method from previously created token';

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'token' => fn() => select(
                label: 'Token ID',
                options: Token::query()->where(['used' => false])->pluck('id'),
                validate: 'uuid',
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
                $this->getToken(),
                ...Account::all(),
            )
        );

        info("Payment method $this->uuid was created");
    }

    public function getToken(): TokenAggregateRoot
    {
        return $this->repository->retrieve(Uuid::fromString($this->argument('token')));
    }
}
