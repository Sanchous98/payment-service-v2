<?php

namespace App\Listeners;

use App\Models\PaymentIntent;
use App\Models\Refund;
use EventSauce\EventSourcing\Message;
use Illuminate\Contracts\Events\Dispatcher;
use PaymentSystem\Enum\RefundStatusEnum;
use PaymentSystem\Events\RefundCanceled;
use PaymentSystem\Events\RefundCreated;
use PaymentSystem\Events\RefundDeclined;

readonly class RefundModelSubscriber
{
    public function create(RefundCreated $event, Message $message): void
    {
        PaymentIntent::query()->findOrFail($event->paymentIntentId, 'id');

        Refund::unguarded(fn() => Refund::query()->create([
            'id' => $message->aggregateRootId(),
            'amount' => $event->money->getAmount(),
            'currency' => $event->money->getCurrency()->getCode(),
            'status' => RefundStatusEnum::CREATED,
            'created_at' => $message->timeOfRecording(),
            'updated_at' => $message->timeOfRecording(),
            'payment_intent_id' => $event->paymentIntentId,
        ]));
    }

    public function cancel(RefundCanceled $event, Message $message): void
    {
        Refund::unguarded(fn() => Refund::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'status' => RefundStatusEnum::CANCELED,
                'updated_at' => $message->timeOfRecording(),
            ]));
    }

    public function decline(RefundDeclined $event, Message $message): void
    {
        Refund::unguarded(fn() => Refund::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'status' => RefundStatusEnum::DECLINED,
                'decline_reason' => $event->reason,
                'updated_at' => $message->timeOfRecording(),
            ]));
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(RefundCreated::class, $this->create(...));
        $events->listen(RefundCanceled::class, $this->cancel(...));
        $events->listen(RefundDeclined::class, $this->decline(...));
    }
}
