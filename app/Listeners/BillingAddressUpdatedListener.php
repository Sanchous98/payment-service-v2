<?php

namespace App\Listeners;

use App\Events\BillingAddressUpdated;
use App\Models\PaymentMethod;
use Illuminate\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use PaymentSystem\Laravel\Jobs\UpdatePaymentMethodJob;
use PaymentSystem\Laravel\Uuid;

readonly class BillingAddressUpdatedListener implements ShouldQueue
{
    public function __construct(private Dispatcher $dispatcher)
    {
    }

    public function __invoke(BillingAddressUpdated $event): void
    {
        $event->billingAddress
            ->paymentMethods()
            ->cursor()
            ->each(fn(PaymentMethod $paymentMethod) => $this->dispatcher
                ->dispatch(new UpdatePaymentMethodJob(Uuid::fromString($paymentMethod->id), $event->billingAddress->toValueObject())));
    }
}
