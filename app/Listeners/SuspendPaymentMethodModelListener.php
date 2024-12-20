<?php

namespace App\Listeners;

use App\Models\PaymentMethod;
use EventSauce\EventSourcing\Message;
use PaymentSystem\Enum\PaymentMethodStatusEnum;
use PaymentSystem\Events\PaymentMethodSuspended;

class SuspendPaymentMethodModelListener
{
    public function __invoke(PaymentMethodSuspended $event, Message $message): void
    {
        PaymentMethod::unguarded(fn() => PaymentMethod::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'status' => PaymentMethodStatusEnum::SUSPENDED,
                'updated_at' => $message->timeOfRecording(),
            ]));
    }
}
