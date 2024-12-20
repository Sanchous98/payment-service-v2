<?php

declare(strict_types=1);

namespace App\Listeners\Gateway;

use App\Models\PaymentIntent;
use EventSauce\EventSourcing\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use PaymentSystem\Gateway\Events\GatewayPaymentIntentCaptured;

class PaymentIntentCapturedListener implements ShouldQueue
{
    public function __invoke(GatewayPaymentIntentCaptured $event, Message $message): void
    {
        PaymentIntent::unguarded(fn() => PaymentIntent::query()
            ->findOrFail($message->aggregateRootId()->toString())
            ->update([
                'fee_amount' => $event->paymentIntent->getFee()?->getAmount(),
                'fee_currency' => $event->paymentIntent->getFee()?->getCurrency()->getCode(),
                'updated_at' => $message->timeOfRecording(),
            ]));
    }
}
