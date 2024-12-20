<?php

namespace App\Listeners;

use App\Models\PaymentIntent;
use App\Models\Refund;
use EventSauce\EventSourcing\Message;
use PaymentSystem\Enum\RefundStatusEnum;
use PaymentSystem\Events\RefundCreated;

class CreateRefundModelListener
{
    public function __invoke(RefundCreated $event, Message $message): void
    {
        Refund::unguarded(function () use ($event, $message) {
            $refund = new Refund([
                'id' => $message->aggregateRootId(),
                'amount' => $event->money->getAmount(),
                'currency' => $event->money->getCurrency()->getCode(),
                'status' => RefundStatusEnum::CREATED,
                'created_at' => $message->timeOfRecording(),
                'updated_at' => $message->timeOfRecording(),
            ]);
            $refund->paymentIntent()->associate(PaymentIntent::query()->findOrFail($event->paymentIntentId));
            $refund->save();
        });
    }
}
