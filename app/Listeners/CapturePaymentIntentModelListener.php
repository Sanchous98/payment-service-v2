<?php

namespace App\Listeners;

use App\Models\PaymentIntent;
use App\Models\PaymentMethod;
use App\Models\Token;
use EventSauce\EventSourcing\Message;
use PaymentSystem\Enum\PaymentIntentStatusEnum;
use PaymentSystem\Events\PaymentIntentCaptured;
use PaymentSystem\PaymentMethodAggregateRoot;
use PaymentSystem\Repositories\TenderRepositoryInterface;
use PaymentSystem\TokenAggregateRoot;

readonly class CapturePaymentIntentModelListener
{
    public function __construct(private TenderRepositoryInterface $repository)
    {
    }

    public function __invoke(PaymentIntentCaptured $event, Message $message): void
    {
        PaymentIntent::unguarded(function () use ($event, $message) {
            $tender = null;

            if ($event->tenderId !== null) {
                $tender = match ($this->repository->retrieve($event->tenderId)::class) {
                    TokenAggregateRoot::class => Token::query()->findOrFail($event->tenderId),
                    PaymentMethodAggregateRoot::class => PaymentMethod::query()->findOrFail($event->tenderId),
                    default => throw new \RuntimeException('unknown tender type'),
                };
            }

            $paymentIntent = PaymentIntent::query()->findOrFail($message->aggregateRootId());
            $paymentIntent->fill([
                'status' => PaymentIntentStatusEnum::SUCCEEDED,
                'updated_at' => $message->timeOfRecording(),
                ...isset($event->amount) ? ['amount' => $event->amount] : [],
            ]);

            if ($tender !== null) {
                $paymentIntent->tender()->associate($tender);
            }
            $paymentIntent->save();
        });
    }
}
