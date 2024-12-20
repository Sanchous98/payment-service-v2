<?php

namespace App\Listeners;

use App\Models\SubscriptionPlan;
use EventSauce\EventSourcing\Message;
use Illuminate\Contracts\Events\Dispatcher;
use PaymentSystem\Events\SubscriptionPlanCreated;
use PaymentSystem\Events\SubscriptionPlanDeleted;
use PaymentSystem\Events\SubscriptionPlanUpdated;

readonly class SubscriptionPlanModelSubscriber
{
    public function create(SubscriptionPlanCreated $event, Message $message): void
    {
        SubscriptionPlan::unguarded(fn() => SubscriptionPlan::query()->create([
            'id' => $message->aggregateRootId()->toString(),
            'name' => $event->name,
            'description' => $event->description,
            'money' => $event->money,
            'interval' => $event->interval,
            'merchant_descriptor' => $event->merchantDescriptor,
            'created_at' => $message->timeOfRecording(),
            'updated_at' => $message->timeOfRecording(),
        ]));
    }

    public function update(SubscriptionPlanUpdated $event, Message $message): void
    {
        SubscriptionPlan::unguarded(fn() => SubscriptionPlan::query()
            ->findOrFail($message->aggregateRootId()->toString())
            ->update(array_filter([
                'name' => $event->name,
                'description' => $event->description,
                'money' => $event->money,
                'interval' => $event->interval,
                'merchant_descriptor' => $event->merchantDescriptor,
                'updated_at' => $message->timeOfRecording(),
            ])));
    }

    public function delete(SubscriptionPlanDeleted $event, Message $message): void
    {
        SubscriptionPlan::query()->where(['id' => $message->aggregateRootId()->toString()])->delete();
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(SubscriptionPlanCreated::class, $this->create(...));
        $events->listen(SubscriptionPlanUpdated::class, $this->update(...));
        $events->listen(SubscriptionPlanDeleted::class, $this->delete(...));
    }
}
