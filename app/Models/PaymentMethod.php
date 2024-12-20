<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use PaymentSystem\Enum\PaymentMethodStatusEnum;
use PaymentSystem\Laravel\Models\Account;
use PaymentSystem\Laravel\Uuid;

class PaymentMethod extends Model
{
    use HasUuids;

    protected $guarded = ['*'];

    protected $casts = [
        'id' => Uuid::class,
        'billing_address_id' => Uuid::class,
        'status' => PaymentMethodStatusEnum::class,
    ];

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(BillingAddress::class);
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class);
    }
}
