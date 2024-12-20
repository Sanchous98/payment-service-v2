<?php

namespace App\Listeners;

use App\Models\PaymentIntent;
use EventSauce\EventSourcing\Message;
use PaymentSystem\Enum\PaymentIntentStatusEnum;
use PaymentSystem\Events\PaymentIntentCanceled;

class CancelPaymentIntentModelListener
{
    public function __invoke(PaymentIntentCanceled $event, Message $message): void
    {
        PaymentIntent::unguarded(fn() => PaymentIntent::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'status' => PaymentIntentStatusEnum::CANCELED,
                'updated_at' => $message->timeOfRecording(),
            ]));
    }
}
