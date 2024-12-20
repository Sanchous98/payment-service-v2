<?php

namespace App\Models;

use App\Casts\CurrencyCast;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Money\Currency;
use Money\Money;
use PaymentSystem\Enum\RefundStatusEnum;

/**
 * @property string $id
 * @property string $amount
 * @property Currency $currency
 * @property RefundStatusEnum $status
 * @property string $decline_reason
 * @property DateTimeInterface $created_at
 * @property DateTimeInterface $updated_at
 *
 * @property-read PaymentIntent $paymentIntent
 */
class Refund extends Model
{
    use HasUuids;

    protected $guarded = ['*'];

    protected $casts = [
        'status' => RefundStatusEnum::class,
        'currency' => CurrencyCast::class,
    ];

    public function paymentIntent(): BelongsTo
    {
        return $this->belongsTo(PaymentIntent::class);
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
