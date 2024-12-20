<?php

namespace App\Listeners;

use App\Models\PaymentIntent;
use App\Models\PaymentMethod;
use App\Models\Token;
use EventSauce\EventSourcing\Message;
use PaymentSystem\Enum\PaymentIntentStatusEnum;
use PaymentSystem\Events\PaymentIntentAuthorized;
use PaymentSystem\Laravel\Messages\AccountDecorator;
use PaymentSystem\Laravel\Models\Account;
use PaymentSystem\PaymentMethodAggregateRoot;
use PaymentSystem\Repositories\TenderRepositoryInterface;
use PaymentSystem\TokenAggregateRoot;

readonly class CreatePaymentIntentModelListener
{
    public function __construct(private TenderRepositoryInterface $repository)
    {
    }

    public function __invoke(PaymentIntentAuthorized $event, Message $message): void
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

            $paymentIntent = new PaymentIntent([
                'id' => $message->aggregateRootId()->toString(),
                'status' => $tender === null ? PaymentIntentStatusEnum::REQUIRES_PAYMENT_METHOD : PaymentIntentStatusEnum::REQUIRES_CAPTURE,
                'merchant_descriptor' => $event->merchantDescriptor,
                'description' => $event->description,
                'amount' => $event->money->getAmount(),
                'currency' => $event->money->getCurrency()->getCode(),
                'created_at' => $message->timeOfRecording(),
                'updated_at' => $message->timeOfRecording(),
            ]);

            $paymentIntent->account()
                ->associate(Account::query()->findOrFail($message->header(AccountDecorator::ACCOUNT_IDS_HEADER)[0]));
            $paymentIntent->tender()->associate($tender);
            $paymentIntent->save();
        });
    }
}
