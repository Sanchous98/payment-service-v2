<?php

namespace App\Listeners;

use App\Models\Token;
use EventSauce\EventSourcing\Message;
use PaymentSystem\Events\TokenDeclined;

class DeclineTokenListener
{
    public function __invoke(TokenDeclined $event, Message $message): void
    {
        Token::unguarded(fn() => Token::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'decline_reason' => $event->reason,
                'updated_at' => $message->timeOfRecording(),
            ]));
    }
}
