<?php

namespace App\Listeners;

use App\Models\BillingAddress;
use App\Models\Card;
use App\Models\PaymentMethod;
use EventSauce\EventSourcing\Message;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use PaymentSystem\Enum\PaymentMethodStatusEnum;
use PaymentSystem\Events\PaymentMethodCreated;
use PaymentSystem\Events\PaymentMethodFailed;
use PaymentSystem\Events\PaymentMethodSuspended;
use PaymentSystem\Events\PaymentMethodUpdated;

readonly class PaymentMethodModelSubscriber
{
    public function __construct(private ConnectionInterface $connection)
    {
    }

    public function create(PaymentMethodCreated $event, Message $message): void
    {
        $this->connection->transaction(fn() => PaymentMethod::unguarded(function () use ($event, $message) {
            $paymentMethod = new PaymentMethod([
                'id' => $message->aggregateRootId()->toString(),
                'status' => PaymentMethodStatusEnum::PENDING,
                'created_at' => $message->timeOfRecording(),
                'updated_at' => $message->timeOfRecording(),
            ]);
            $paymentMethod->source()
                ->associate(tap(Card::fromValueObject($event->source, $message->timeOfRecording()))->save());
            $paymentMethod->billingAddress()
                ->associate(BillingAddress::query()->findOrFail($event->billingAddress->id));
            $paymentMethod->save();
        }));
    }

    public function update(PaymentMethodUpdated $event, Message $message): void
    {
        PaymentMethod::unguarded(fn() => tap(PaymentMethod::query()
            ->findOrFail($message->aggregateRootId()), function (PaymentMethod $paymentMethod) use ($message, $event) {
            $paymentMethod->billingAddress()
                ->associate(BillingAddress::query()->findOrFail($event->billingAddress->id));
        })
            ->update([
                'updated_at' => $message->timeOfRecording(),
            ]));
    }

    public function suspend(PaymentMethodSuspended $event, Message $message): void
    {
        PaymentMethod::unguarded(fn() => PaymentMethod::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'status' => PaymentMethodStatusEnum::SUSPENDED,
                'updated_at' => $message->timeOfRecording(),
            ]));
    }

    public function fail(PaymentMethodFailed $event, Message $message): void
    {
        PaymentMethod::unguarded(fn() => PaymentMethod::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'status' => PaymentMethodStatusEnum::FAILED,
                'updated_at' => $message->timeOfRecording(),
            ]));
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(PaymentMethodCreated::class, $this->create(...));
        $events->listen(PaymentMethodUpdated::class, $this->update(...));
        $events->listen(PaymentMethodSuspended::class, $this->suspend(...));
        $events->listen(PaymentMethodFailed::class, $this->fail(...));
    }
}
