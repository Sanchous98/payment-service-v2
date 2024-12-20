<?php

declare(strict_types=1);

namespace App\Listeners\Gateway;

use App\Models\PaymentMethod;
use EventSauce\EventSourcing\Message;
use PaymentSystem\Enum\PaymentMethodStatusEnum;
use PaymentSystem\Gateway\Events\GatewayPaymentMethodAdded;

class SucceedPaymentMethodModelListener
{
    public function __invoke(GatewayPaymentMethodAdded $event, Message $message): void
    {
        PaymentMethod::unguarded(fn() => PaymentMethod::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'status' => PaymentMethodStatusEnum::SUCCEEDED,
                'updated_at' => $message->timeOfRecording(),
            ]));
    }
}
