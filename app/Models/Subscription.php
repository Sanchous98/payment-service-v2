<?php

namespace App\Models;

use DateInterval;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PaymentSystem\Enum\SubscriptionStatusEnum;
use PaymentSystem\Laravel\Models\Account;
use PaymentSystem\Laravel\Uuid;

class Subscription extends Model
{
    use HasUuids;

    protected $fillable = ['payment_method_id'];

    protected $casts = [
        'id' => Uuid::class,
        'account_id' => Uuid::class,
        'payment_method_id' => Uuid::class,
        'subscription_plan_id' => Uuid::class,
        'status' => SubscriptionStatusEnum::class,
        'ends_at' => 'datetime',
    ];

    public function ended(): Attribute
    {
        return Attribute::get(fn() => $this->endsAt->sub(new DateInterval('P1D'))->isPast());
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function paymentIntents(): HasMany
    {
        return $this->hasMany(PaymentIntent::class)->latest();
    }
}
