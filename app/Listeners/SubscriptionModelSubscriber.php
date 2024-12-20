<?php

namespace App\Listeners;

use App\Models\Subscription;
use EventSauce\EventSourcing\Message;
use Illuminate\Contracts\Events\Dispatcher;
use PaymentSystem\Enum\SubscriptionStatusEnum;
use PaymentSystem\Events\SubscriptionCanceled;
use PaymentSystem\Events\SubscriptionCreated;
use PaymentSystem\Events\SubscriptionPaid;
use PaymentSystem\Laravel\Messages\AccountDecorator;
use PaymentSystem\Repositories\SubscriptionRepositoryInterface;

readonly class SubscriptionModelSubscriber
{
    public function __construct(private SubscriptionRepositoryInterface $repository)
    {
    }

    public function create(SubscriptionCreated $event, Message $message): void
    {
        Subscription::unguarded(fn() => Subscription::query()->create([
            'id' => $message->aggregateRootId(),
            'status' => SubscriptionStatusEnum::PENDING,
            'subscription_plan_id' => $event->plan->id,
            'payment_method_id' => $event->paymentMethodId,
            'account_id' => $message->header(AccountDecorator::ACCOUNT_IDS_HEADER)[0],
            'ends_at' => $message->timeOfRecording(),
            'created_at' => $message->timeOfRecording(),
            'updated_at' => $message->timeOfRecording(),
        ]));
    }

    public function pay(SubscriptionPaid $event, Message $message): void
    {
        Subscription::unguarded(fn() => Subscription::query()
            ->findOrFail($message->aggregateRootId()))
            ->update([
                'status' => SubscriptionStatusEnum::ACTIVE,
                'ends_at' => $this->repository->retrieve($message->aggregateRootId())->endsAt(),
                'updated_at' => $message->timeOfRecording(),
            ]);
    }

    public function cancel(SubscriptionCanceled $event, Message $message): void
    {
        Subscription::unguarded(fn() => Subscription::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'status' => SubscriptionStatusEnum::CANCELED,
                'ends_at' => $message->timeOfRecording(),
                'updated_at' => $message->timeOfRecording(),
            ]));
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(SubscriptionCreated::class, $this->create(...));
        $events->listen(SubscriptionPaid::class, $this->pay(...));
        $events->listen(SubscriptionCanceled::class, $this->cancel(...));
    }
}
