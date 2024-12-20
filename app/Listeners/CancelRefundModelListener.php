<?php

namespace App\Listeners;

use App\Models\Refund;
use EventSauce\EventSourcing\Message;
use PaymentSystem\Enum\RefundStatusEnum;
use PaymentSystem\Events\RefundCanceled;

class CancelRefundModelListener
{
    public function __invoke(RefundCanceled $event, Message $message): void
    {
        Refund::unguarded(fn() => Refund::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'status' => RefundStatusEnum::CANCELED,
                'updated_at' => $message->timeOfRecording(),
            ]));
    }
}
