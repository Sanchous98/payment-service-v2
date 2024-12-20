<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PaymentMethod;
use App\Models\Token;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Str;
use Money\Currency;
use Money\Money;
use PaymentSystem\Enum\PaymentMethodStatusEnum;
use PaymentSystem\Laravel\Jobs\AuthorizePaymentIntentJob;
use PaymentSystem\Laravel\Models\Account;
use PaymentSystem\Laravel\Uuid;
use PaymentSystem\Repositories\PaymentMethodRepositoryInterface;
use PaymentSystem\Repositories\TokenRepositoryInterface;
use PaymentSystem\TenderInterface;
use PaymentSystem\ValueObjects\MerchantDescriptor;
use Symfony\Component\Intl\Currencies;

use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class PaymentIntentAuthorize extends Command implements PromptsForMissingInput
{
    private const PAYMENT_METHOD_TENDER = 'payment_method';

    private const TOKEN_TENDER = 'token';
    protected $signature = 'payments:payment-intent:authorize
                            {account : Account which should be used for payment}
                            {tenderType : Payment method or token}
                            {token : Token ID}
                            {paymentMethod : Payment method ID}
                            {currency : Currency}
                            {amount : Amount}
                            {merchantDescriptor : Merchant descriptor}
                            {description : Description}';
    protected $description = 'Authorize payment';
    private readonly Uuid $uuid;

    public function __construct(
        private readonly TokenRepositoryInterface $tokenRepository,
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
    ) {
        parent::__construct();

        $this->uuid = new Uuid(Str::orderedUuid());
    }

    public function __invoke(Dispatcher $dispatcher): void
    {
        $dispatcher->dispatch(new AuthorizePaymentIntentJob(
            $this->uuid,
            $this->getMoney(),
            Account::query()->find($this->argument('account')),
            $this->getTender(),
            new MerchantDescriptor($this->argument('merchantDescriptor') ?: ''),
            $this->argument('description') ?: '',
        ));

        info("Payment intent $this->uuid authorized successfully");
    }

    public function getMoney(): Money
    {
        return new Money($this->argument('amount'), new Currency($this->argument('currency')));
    }

    public function getTender(): TenderInterface
    {
        return match ($this->argument('tenderType')) {
            'token' => $this->tokenRepository->retrieve(Uuid::fromString($this->argument('token'))),
            'payment_method' => $this->paymentMethodRepository->retrieve(
                Uuid::fromString($this->argument('paymentMethod'))
            ),
            default => throw new \RuntimeException('unknown tender type'),
        };
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'account' => fn() => select(
                label: 'Account which should be used for payment',
                options: Account::query()->get(['id', 'description'])
                    ->flatMap(fn(Account $account) => [(string)$account->id => $account->description]),
            ),
            'tenderType' => fn() => select(
                label: 'Payment method or token',
                options: [
                    'payment_method' => 'Payment method',
                    'token' => 'Token',
                ],
            ),
            ...match ($this->argument('tenderType')) {
                self::PAYMENT_METHOD_TENDER => [
                    'paymentMethod' => fn() => select(
                        label: 'Payment method',
                        options: PaymentMethod::query()
                            ->where('status', PaymentMethodStatusEnum::SUCCEEDED)
                            ->pluck('id'),
                    ),
                    'token' => fn() => null,
                ],
                self::TOKEN_TENDER => [
                    'token' => fn() => select(
                        label: 'Token',
                        options: Token::query()->where(['used' => false])->pluck('id'),
                    ),
                    'paymentMethod' => fn() => null,
                ],
                default => [],
            },
            'currency' => fn() => select(
                label: 'Currency',
                options: collect(Currencies::getCurrencyCodes())
                    ->flatMap(fn(string $code) => [$code => "$code (" . Currencies::getName($code) . ")"]),
                validate: 'currency',
            ),
            'amount' => fn() => text(
                label: 'Amount',
                required: true,
                validate: 'numeric',
            ),
            'merchantDescriptor' => fn() => text('Merchant descriptor'),
            'description' => fn() => text('Description (Optional)'),
        ];
    }
}
