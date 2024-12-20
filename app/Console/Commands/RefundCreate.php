<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PaymentIntent;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Str;
use Money\Currency;
use Money\Money;
use PaymentSystem\Enum\PaymentIntentStatusEnum;
use PaymentSystem\Laravel\Jobs\CreateRefundJob;
use PaymentSystem\Laravel\Uuid;
use PaymentSystem\Repositories\PaymentIntentRepositoryInterface;

use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class RefundCreate extends Command implements PromptsForMissingInput
{
    protected $signature = 'payments:refund:create
                            {paymentIntent : Succeeded payment intent}
                            {amount : Amount to refund}';
    protected $description = 'Refund a payment';
    private readonly Uuid $uuid;

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'paymentIntent' => fn() => select(
                label: 'Payment intent',
                options: PaymentIntent::with('refunds')
                    ->where(['status' => PaymentIntentStatusEnum::SUCCEEDED])
                    ->get()
                    ->filter(fn(PaymentIntent $paymentIntent) => $paymentIntent->availableForRefund->isPositive())
                    ->pluck('id'),
            ),
            'amount' => fn() => text(
                label: 'Amount to refund',
                default: ($paymentIntent = PaymentIntent::with('refunds')->findOrFail($this->argument('paymentIntent')))->availableForRefund->getAmount(),
                validate: "numeric|max:{$paymentIntent->availableForRefund->getAmount()}",
            ),
        ];
    }

    public function __construct(
        private readonly PaymentIntentRepositoryInterface $repository,
    ) {
        parent::__construct();

        $this->uuid = new Uuid(Str::orderedUuid());
    }

    public function __invoke(Dispatcher $dispatcher): void
    {
        $paymentIntent = PaymentIntent::with(['account', 'refunds'])->findOrFail($this->argument('paymentIntent'));
        $amount = $this->argument('amount') ?: $paymentIntent->availableForRefund->getAmount();
        $currency = $paymentIntent->currency;

        $dispatcher->dispatch(
            new CreateRefundJob(
                $this->uuid,
                $this->repository->retrieve(Uuid::fromString($this->argument('paymentIntent'))),
                new Money($amount, new Currency($currency)),
                $paymentIntent->account,
            )
        );

        info("Refund $this->uuid created successfully");
    }
}
