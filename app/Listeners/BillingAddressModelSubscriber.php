<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\BillingAddress;
use EventSauce\EventSourcing\Message;
use Illuminate\Contracts\Events;
use PaymentSystem\Events\BillingAddressCreated;
use PaymentSystem\Events\BillingAddressDeleted;
use PaymentSystem\Events\BillingAddressUpdated;

readonly class BillingAddressModelSubscriber
{
    public function create(BillingAddressCreated $event, Message $message): void
    {
        BillingAddress::unguarded(fn() => BillingAddress::query()->create([
            'id' => $message->aggregateRootId()->toString(),
            'first_name' => $event->firstName,
            'last_name' => $event->lastName,
            'country' => $event->country,
            'city' => $event->city,
            'postal_code' => $event->postalCode,
            'email' => $event->email,
            'phone' => $event->phone,
            'address_line' => $event->addressLine,
            'address_line_extra' => $event->addressLineExtra,
            'created_at' => $message->timeOfRecording(),
            'updated_at' => $message->timeOfRecording(),
        ]));
    }

    public function update(BillingAddressUpdated $event, Message $message): void
    {
        BillingAddress::unguarded(fn() => BillingAddress::query()
            ->where(['id' => $message->aggregateRootId()])
            ->update(array_filter([
                'first_name' => $event->firstName,
                'last_name' => $event->lastName,
                'country' => $event->country,
                'city' => $event->city,
                'postal_code' => $event->postalCode,
                'email' => $event->email,
                'phone' => $event->phone,
                'address_line' => $event->addressLine,
                'address_line_extra' => $event->addressLineExtra,
                'updated_at' => $message->timeOfRecording(),
            ])));
    }

    public function delete(BillingAddressDeleted $event, Message $message): void
    {
        BillingAddress::query()->where(['id' => $message->aggregateRootId()])->delete();
    }

    public function subscribe(Events\Dispatcher $events): void
    {
        $events->listen(BillingAddressCreated::class, $this->create(...));
        $events->listen(BillingAddressUpdated::class, $this->update(...));
        $events->listen(BillingAddressDeleted::class, $this->delete(...));
    }
}
