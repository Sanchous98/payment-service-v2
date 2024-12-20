<?php

namespace App\Listeners;

use App\Models\Refund;
use EventSauce\EventSourcing\Message;
use PaymentSystem\Enum\RefundStatusEnum;
use PaymentSystem\Events\RefundDeclined;

class DeclineRefundModelListener
{
    public function __invoke(RefundDeclined $event, Message $message): void
    {
        Refund::unguarded(fn() => Refund::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'status' => RefundStatusEnum::DECLINED,
                'decline_reason' => $event->reason,
                'updated_at' => $message->timeOfRecording(),
            ]));
    }
}
