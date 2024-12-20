<?php

namespace App\Listeners;

use App\Models\Refund;
use EventSauce\EventSourcing\Message;
use PaymentSystem\Enum\RefundStatusEnum;
use PaymentSystem\Events\RefundSucceeded;

class SucceedRefundModelListener
{
    public function __invoke(RefundSucceeded $event, Message $message): void
    {
        Refund::unguarded(fn() => Refund::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'status' => RefundStatusEnum::SUCCEEDED,
                'updated_at' => $message->timeOfRecording(),
            ]));
    }
}
