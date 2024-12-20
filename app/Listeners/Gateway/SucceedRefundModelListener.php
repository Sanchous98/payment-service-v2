<?php

declare(strict_types=1);

namespace App\Listeners\Gateway;

use App\Models\Refund;
use EventSauce\EventSourcing\Message;
use PaymentSystem\Enum\RefundStatusEnum;
use PaymentSystem\Gateway\Events\GatewayRefundCreated;

class SucceedRefundModelListener
{
    public function __invoke(GatewayRefundCreated $event, Message $message): void
    {
        Refund::unguarded(fn() => Refund::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'status' => RefundStatusEnum::SUCCEEDED,
                'updated_at' => $message->timeOfRecording(),
            ]));
    }
}
