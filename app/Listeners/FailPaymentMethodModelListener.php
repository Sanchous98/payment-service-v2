<?php

namespace App\Listeners;

use App\Models\PaymentMethod;
use EventSauce\EventSourcing\Message;
use PaymentSystem\Enum\PaymentMethodStatusEnum;
use PaymentSystem\Events\PaymentMethodFailed;

class FailPaymentMethodModelListener
{
    public function __invoke(PaymentMethodFailed $event, Message $message): void
    {
        PaymentMethod::unguarded(fn() => PaymentMethod::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'status' => PaymentMethodStatusEnum::FAILED,
                'updated_at' => $message->timeOfRecording(),
            ]));
    }
}
