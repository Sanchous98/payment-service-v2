<?php

namespace App\Listeners;

use App\Models\Token;
use EventSauce\EventSourcing\Message;
use PaymentSystem\Events\TokenUsed;

class UseTokenModelListener
{
    public function __invoke(TokenUsed $event, Message $message): void
    {
        Token::unguarded(fn() => Token::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'used' => true,
                'updated_at' => $message->timeOfRecording(),
            ]));
    }
}
