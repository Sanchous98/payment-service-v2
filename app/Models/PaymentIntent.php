<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
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
use PaymentSystem\Laravel\Uuid;

class PaymentIntent extends Model
{
    use HasUuids;

    protected $guarded = ['*'];

    protected $casts = [
        'id' => Uuid::class,
        'account_id' => Uuid::class,
        'tender_id' => Uuid::class,
        'subscription_id' => Uuid::class,
        'money' => MoneyCast::class,
        'fee' => MoneyCast::class . ':fee_',
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

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function availableForRefund(): Attribute
    {
        return Attribute::get(fn() => new Money($this->amount, $this->currency)
            ->subtract(...$this->refunds->pluck('money')) );
    }
}
