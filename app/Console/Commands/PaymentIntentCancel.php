<?php

namespace App\Console\Commands;

use App\Models\PaymentIntent;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use PaymentSystem\Enum\PaymentIntentStatusEnum;
use PaymentSystem\Laravel\Jobs\CancelPaymentIntentJob;
use PaymentSystem\Laravel\Uuid;

use function Laravel\Prompts\info;
use function Laravel\Prompts\select;

class PaymentIntentCancel extends Command implements PromptsForMissingInput
{
    protected $signature = 'payments:payment-intent:cancel
                            {paymentIntent : Payment Intent ID}';

    protected $description = 'Cancel payment intent';

    public function __invoke(Dispatcher $dispatcher): void
    {
        $dispatcher->dispatch(
            new CancelPaymentIntentJob(
                Uuid::fromString($this->argument('paymentIntent')),
                PaymentIntent::with('account')->find($this->argument('paymentIntent'))->account,
            )
        );

        info("Payment intent {$this->argument('paymentIntent')} canceled successfully");
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
            )
        ];
    }
}
