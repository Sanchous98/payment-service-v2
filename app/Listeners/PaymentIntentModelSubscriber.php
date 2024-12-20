<?php

namespace App\Listeners;

use App\Models\PaymentIntent;
use App\Models\PaymentMethod;
use App\Models\Token;
use EventSauce\EventSourcing\Message;
use Illuminate\Contracts\Events\Dispatcher;
use PaymentSystem\Enum\PaymentIntentStatusEnum;
use PaymentSystem\Events\PaymentIntentAuthorized;
use PaymentSystem\Events\PaymentIntentCanceled;
use PaymentSystem\Events\PaymentIntentCaptured;
use PaymentSystem\Laravel\Messages\AccountDecorator;
use PaymentSystem\PaymentMethodAggregateRoot;
use PaymentSystem\Repositories\TenderRepositoryInterface;
use PaymentSystem\TokenAggregateRoot;

readonly class PaymentIntentModelSubscriber
{
    public function __construct(private TenderRepositoryInterface $repository)
    {
    }

    public function create(PaymentIntentAuthorized $event, Message $message): void
    {
        $tender = $event->tenderId === null ? null : match ($this->repository->retrieve($event->tenderId)::class) {
            TokenAggregateRoot::class => Token::query()->findOrFail($event->tenderId),
            PaymentMethodAggregateRoot::class => PaymentMethod::query()->findOrFail($event->tenderId),
            default => throw new \RuntimeException('unknown tender type'),
        };

        PaymentIntent::unguarded(function () use ($event, $message, $tender) {
            $paymentIntent = new PaymentIntent([
                'id' => $message->aggregateRootId()->toString(),
                'status' => $tender === null ? PaymentIntentStatusEnum::REQUIRES_PAYMENT_METHOD : PaymentIntentStatusEnum::REQUIRES_CAPTURE,
                'merchant_descriptor' => $event->merchantDescriptor,
                'description' => $event->description,
                'amount' => $event->money->getAmount(),
                'currency' => $event->money->getCurrency()->getCode(),
                'created_at' => $message->timeOfRecording(),
                'updated_at' => $message->timeOfRecording(),
                'subscription_id' => $event->subscriptionId,
            ]);

            $paymentIntent->account()->associate($message->header(AccountDecorator::ACCOUNT_IDS_HEADER)[0]);
            $paymentIntent->tender()->associate($tender);
            $paymentIntent->save();
        });
    }

    public function capture(PaymentIntentCaptured $event, Message $message): void
    {
        $tender = $event->tenderId === null ? null : match ($this->repository->retrieve($event->tenderId)::class) {
            TokenAggregateRoot::class => Token::query()->findOrFail($event->tenderId),
            PaymentMethodAggregateRoot::class => PaymentMethod::query()->findOrFail($event->tenderId),
            default => throw new \RuntimeException('unknown tender type'),
        };

        $paymentIntent = PaymentIntent::query()->findOrFail($message->aggregateRootId());
        $tender === null || $paymentIntent->tender()->associate($tender);

        PaymentIntent::unguarded(fn() => $paymentIntent->update([
            'status' => PaymentIntentStatusEnum::SUCCEEDED,
            'updated_at' => $message->timeOfRecording(),
            ...isset($event->amount) ? ['amount' => $event->amount] : [],
        ]));
    }

    public function cancel(PaymentIntentCanceled $event, Message $message): void
    {
        PaymentIntent::unguarded(fn() => PaymentIntent::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'status' => PaymentIntentStatusEnum::CANCELED,
                'updated_at' => $message->timeOfRecording(),
            ]));
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(PaymentIntentAuthorized::class, $this->create(...));
        $events->listen(PaymentIntentCaptured::class, $this->capture(...));
        $events->listen(PaymentIntentCanceled::class, $this->cancel(...));
    }
}
