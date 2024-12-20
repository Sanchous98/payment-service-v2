<?php

namespace App\Listeners;

use App\Models\Card;
use App\Models\Token;
use EventSauce\EventSourcing\Message;
use Illuminate\Database\ConnectionInterface;
use PaymentSystem\Events\TokenCreated;

readonly class CreateTokenModelListener
{
    public function __construct(private ConnectionInterface $connection)
    {
    }

    public function __invoke(TokenCreated $event, Message $message): void
    {
        $this->connection->transaction(fn() => Token::unguarded(function () use ($message, $event) {
            $token = new Token([
                'id' => $message->aggregateRootId(),
                'created_at' => $message->timeOfRecording(),
                'updated_at' => $message->timeOfRecording(),
            ]);
            $token->card()->associate(tap(Card::fromValueObject($event->source, $message->timeOfRecording()))->save());
            $token->save();
        }));
    }
}
