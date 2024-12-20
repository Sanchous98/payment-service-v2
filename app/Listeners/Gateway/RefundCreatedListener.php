<?php

declare(strict_types=1);

namespace App\Listeners\Gateway;

use App\Models\Refund;
use EventSauce\EventSourcing\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use PaymentSystem\Enum\RefundStatusEnum;
use PaymentSystem\Gateway\Events\GatewayRefundCreated;

class RefundCreatedListener implements ShouldQueue
{
    public function __invoke(GatewayRefundCreated $event, Message $message): void
    {
        Refund::unguarded(fn() => Refund::query()
            ->findOrFail($message->aggregateRootId()->toString())
            ->update([
                'status' => RefundStatusEnum::SUCCEEDED,
                'fee_amount' => $event->refund->getFee()?->getAmount(),
                'fee_currency' => $event->refund->getFee()?->getCurrency()->getCode(),
                'updated_at' => $message->timeOfRecording(),
            ]));
    }
}
