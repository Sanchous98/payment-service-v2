<?php

namespace App\Listeners;

use App\Models\Card;
use App\Models\Token;
use EventSauce\EventSourcing\Message;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use PaymentSystem\Events\TokenCreated;
use PaymentSystem\Events\TokenDeclined;
use PaymentSystem\Events\TokenUsed;

readonly class TokenModelSubscriber
{
    public function __construct(private ConnectionInterface $connection)
    {
    }

    public function create(TokenCreated $event, Message $message): void
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

    public function use(TokenUsed $event, Message $message): void
    {
        Token::unguarded(fn() => Token::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'used' => true,
                'updated_at' => $message->timeOfRecording(),
            ]));
    }

    public function decline(TokenDeclined $event, Message $message): void
    {
        Token::unguarded(fn() => Token::query()
            ->findOrFail($message->aggregateRootId())
            ->update([
                'decline_reason' => $event->reason,
                'updated_at' => $message->timeOfRecording(),
            ]));
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(TokenCreated::class, $this->create(...));
        $events->listen(TokenUsed::class, $this->use(...));
        $events->listen(TokenDeclined::class, $this->decline(...));
    }
}
