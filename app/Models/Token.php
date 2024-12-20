<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PaymentSystem\Laravel\Uuid;
use PaymentSystem\ValueObjects\CreditCard;

class Token extends Model
{
    use HasUuids;

    protected $casts = [
        'id' => Uuid::class,
        'card_id' => Uuid::class,
        'used' => 'boolean',
    ];

    protected $guarded = ['*'];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function type(): Attribute
    {
        return Attribute::get(fn() => CreditCard::TYPE);
    }
}
