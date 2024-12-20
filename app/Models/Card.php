<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use PaymentSystem\ValueObjects\CreditCard;

/**
 * @property-read string $id
 * @property-read string $first6 @todo CardNumber cast
 * @property-read string $last4
 * @property-read string $brand
 * @property-read string $holder @todo Holder cast
 * @property-read int $expiration_month @todo Expiration cast
 * @property-read int $expiration_year
 * @property-read DateTimeInterface $created_at
 * @property-read DateTimeInterface $updated_at
 */
class Card extends Model
{
    use HasUuids;

    protected $casts = [
        'year' => 'integer',
    ];

    protected $guarded = ['*'];

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
