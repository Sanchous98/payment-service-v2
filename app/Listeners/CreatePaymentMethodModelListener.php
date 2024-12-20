<?php

namespace App\Listeners;

use App\Models\BillingAddress;
use App\Models\Card;
use App\Models\PaymentMethod;
use EventSauce\EventSourcing\Message;
use Illuminate\Database\ConnectionInterface;
use PaymentSystem\Enum\PaymentMethodStatusEnum;
use PaymentSystem\Events\PaymentMethodCreated;

readonly class CreatePaymentMethodModelListener
{
    public function __construct(private ConnectionInterface $connection)
    {
    }

    public function __invoke(PaymentMethodCreated $event, Message $message): void
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
                ->associate(tap(BillingAddress::fromValueObject($event->billingAddress, $message->timeOfRecording()))->save());
            $paymentMethod->save();
        }));
    }
}
