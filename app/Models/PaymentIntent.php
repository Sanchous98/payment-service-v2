<?php

namespace App\Models;

use App\Casts\CurrencyCast;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Money\Currency;
use Money\Money;
use PaymentSystem\Enum\PaymentIntentStatusEnum;
use PaymentSystem\Laravel\Models\Account;

/**
 * @property string $id
 * @property string $amount
 * @property Currency $currency
 * @property string $description
 * @property string $merchant_descriptor
 * @property DateTimeInterface $created_at
 * @property DateTimeInterface $updated_at
 * @property PaymentIntentStatusEnum $status
 *
 * @property Money $fee
 * @property-read Money $availableForRefund
 * @property-read Account $account
 * @property-read PaymentMethod|Token $tender
 * @property-read Collection<string, Refund> $refunds
 */
class PaymentIntent extends Model
{
    use HasUuids;

    protected $guarded = ['*'];

    protected $casts = [
        'currency' => CurrencyCast::class,
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
