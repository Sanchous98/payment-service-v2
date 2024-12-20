<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use App\Casts\ValueObjectCast;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Money\Currency;
use PaymentSystem\Enum\RefundStatusEnum;
use PaymentSystem\Laravel\Uuid;

class Refund extends Model
{
    use HasUuids;

    protected $guarded = ['*'];

    protected $casts = [
        'id' => Uuid::class,
        'payment_intent_id' => Uuid::class,
        'money' => MoneyCast::class,
        'fee' => MoneyCast::class . ':fee_',
        'status' => RefundStatusEnum::class,
        'currency' => ValueObjectCast::class . ':' . Currency::class,
    ];

    public function paymentIntent(): BelongsTo
    {
        return $this->belongsTo(PaymentIntent::class);
    }
}
