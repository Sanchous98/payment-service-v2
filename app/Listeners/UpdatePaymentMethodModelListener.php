<?php

namespace App\Listeners;

use App\Models\BillingAddress;
use App\Models\PaymentMethod;
use EventSauce\EventSourcing\Message;
use PaymentSystem\Events\PaymentMethodUpdated;

class UpdatePaymentMethodModelListener
{
    public function __invoke(PaymentMethodUpdated $event, Message $message): void
    {
        PaymentMethod::unguarded(fn() => tap(PaymentMethod::query()
            ->findOrFail($message->aggregateRootId()), function (PaymentMethod $paymentMethod) use ($message, $event) {
                $paymentMethod->billingAddress()
                    ->associate(BillingAddress::query()->where($event->billingAddress->jsonSerialize())->first());
            })
            ->update([
                'updated_at' => $message->timeOfRecording(),
            ]));
    }
}
