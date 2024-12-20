<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use PaymentSystem\Enum\PaymentMethodStatusEnum;
use PaymentSystem\Laravel\Models\Account;

/**
 * @property string $id
 * @property Card $source
 * @property BillingAddress $billingAddress
 * @property PaymentMethodStatusEnum $status
 *
 * @property Collection<Account> $accounts
 * @property DateTimeInterface $created_at
 * @property DateTimeInterface $updated_at
 */
class PaymentMethod extends Model
{
    use HasUuids;

    protected $guarded = ['*'];

    protected $casts = [
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
