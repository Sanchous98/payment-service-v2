<?php

namespace App\Listeners\Gateway;

use App\Models\PaymentMethod;
use EventSauce\EventSourcing\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use PaymentSystem\Enum\PaymentMethodStatusEnum;
use PaymentSystem\Gateway\Events\GatewayPaymentMethodAdded;

class PaymentMethodAddedListener implements ShouldQueue
{
    public function __invoke(GatewayPaymentMethodAdded $event, Message $message): void
    {
        $paymentMethod = PaymentMethod::query()->findOrFail($message->aggregateRootId());
        $paymentMethod->accounts()->attach($event->paymentMethod->getGatewayId());
        $paymentMethod->status = PaymentMethodStatusEnum::SUCCEEDED;
        $paymentMethod->push();
    }
}
