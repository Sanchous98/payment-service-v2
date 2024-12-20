<?php

namespace App\Console\Commands;

use App\Models\PaymentIntent;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use PaymentSystem\Enum\PaymentIntentStatusEnum;
use PaymentSystem\Laravel\Jobs\CapturePaymentIntentJob;
use PaymentSystem\Laravel\Uuid;
use PaymentSystem\Repositories\PaymentIntentRepositoryInterface;

use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class PaymentIntentCapture extends Command implements PromptsForMissingInput
{
    protected $signature = 'payments:payment-intent:capture
                            {paymentIntent : Payment Intent}
                            {amount : Amount to capture}';

    protected $description = 'Capture payment intent';

    public function __invoke(Dispatcher $dispatcher, PaymentIntentRepositoryInterface $repository): void
    {
        $dispatcher->dispatch(
            new CapturePaymentIntentJob(
                Uuid::fromString($this->argument('paymentIntent')),
                PaymentIntent::with('account')->find($this->argument('paymentIntent'))->account,
                $this->argument('amount') ?: null
            )
        );

        info("Payment intent {$this->argument('paymentIntent')} captured successfully");
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'paymentIntent' => fn() => select(
                label: 'Payment Intent',
                options: PaymentIntent::query()
                    ->whereIn(
                        'status',
                        [PaymentIntentStatusEnum::REQUIRES_CAPTURE, PaymentIntentStatusEnum::REQUIRES_PAYMENT_METHOD]
                    )
                    ->pluck('id'),
            ),
            'amount' => fn() => text(
                label: 'Amount',
                validate: 'numeric|max:' . PaymentIntent::query()->find($this->argument('paymentIntent'))->amount,
            ),
        ];
    }
}
