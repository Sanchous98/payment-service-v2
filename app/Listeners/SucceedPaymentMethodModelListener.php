<?php

namespace App\Listeners;

use App\Models\PaymentMethod;
use EventSauce\EventSourcing\Message;
use PaymentSystem\Enum\PaymentMethodStatusEnum;
use PaymentSystem\Events\PaymentMethodSucceeded;

class SucceedPaymentMethodModelListener
{
    public function __invoke(PaymentMethodSucceeded $event, Message $message): void
    {
        PaymentMethod::unguarded(fn() => PaymentMethod::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'status' => PaymentMethodStatusEnum::SUCCEEDED,
                'updated_at' => $message->timeOfRecording(),
            ]));
    }
}
