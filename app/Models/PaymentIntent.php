<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\ValueObjectCast;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Money\Currency;
use Money\Money;
use PaymentSystem\Enum\PaymentIntentStatusEnum;
use PaymentSystem\Laravel\Models\Account;

class PaymentIntent extends Model
{
    use HasUuids;

    protected $guarded = ['*'];

    protected $casts = [
        'currency' => ValueObjectCast::class . ':' . Currency::class,
        'status' => PaymentIntentStatusEnum::class,
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function tender(): MorphTo
    {
        return $this->morphTo();
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function availableForRefund(): Attribute
    {
        return Attribute::get(fn() => (new Money($this->amount, $this->currency))
            ->subtract(...$this->refunds->map(fn(Refund $refund) => new Money($refund->amount, $refund->currency))) );
    }

    public function fee(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->getAttribute('fee_amount') !== null && $this->getAttribute('fee_currency') !== null) {
                    return new Money($this->getAttribute('fee_amount'), new Currency($this->getAttribute('fee_currency')));
                }

                return null;
            },
            set: fn(?Money $money) => $this->setRawAttributes([
                'fee_amount' => $money?->getAmount(),
                'fee_currency' => $money?->getCurrency()->getCode(),
            ])
        );
    }
}
