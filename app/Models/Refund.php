<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\ValueObjectCast;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Money\Currency;
use Money\Money;
use PaymentSystem\Enum\RefundStatusEnum;

class Refund extends Model
{
    use HasUuids;

    protected $guarded = ['*'];

    protected $casts = [
        'status' => RefundStatusEnum::class,
        'currency' => ValueObjectCast::class . ':' . Currency::class,
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
