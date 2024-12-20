<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PaymentSystem\Contracts\SourceInterface;
use PaymentSystem\ValueObjects\CreditCard;

/**
 * @property string $id
 * @property bool $used
 * @property string $decline_reason
 * @property DateTimeInterface $created_at
 * @property DateTimeInterface $updated_at
 *
 * @property-read SourceInterface::TYPE $type
 * @property Card $card
 */
class Token extends Model
{
    use HasUuids;

    protected $casts = ['used' => 'boolean'];

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
