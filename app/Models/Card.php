<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use PaymentSystem\Laravel\Uuid;
use PaymentSystem\ValueObjects\CreditCard;

class Card extends Model
{
    use HasUuids;

    protected $guarded = ['*'];

    protected $casts = [
        'id' => Uuid::class,
    ];

    public static function fromValueObject(CreditCard $card, DateTimeInterface $date): self
    {
        return self::unguarded(fn() => new self([
            'first6' => $card->number->first6,
            'last4' => $card->number->last4,
            'brand' => $card->number->brand,
            'expiration_month' => $card->expiration->format('n'),
            'expiration_year' => $card->expiration->format('Y'),
            'holder' => (string)$card->holder,
            'created_at' => $date,
            'updated_at' => $date,
        ]));
    }
}
