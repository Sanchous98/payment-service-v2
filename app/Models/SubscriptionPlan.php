<?php

namespace App\Models;

use App\Casts\DateIntervalCast;
use App\Casts\MoneyCast;
use App\Casts\ValueObjectCast;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Money\Currency;
use PaymentSystem\Entities\SubscriptionPlan as SubscriptionPlanEntity;
use PaymentSystem\Laravel\Uuid;
use PaymentSystem\ValueObjects\MerchantDescriptor;

class SubscriptionPlan extends Model
{
    use HasUuids;

    protected $guarded = ['*'];

    protected $casts = [
        'id' => Uuid::class,
        'money' => MoneyCast::class,
        'currency' => ValueObjectCast::class . ':' . Currency::class,
        'interval' => DateIntervalCast::class,
        'merchant_descriptor' => ValueObjectCast::class . ':' . MerchantDescriptor::class,
    ];

    public function intervalUnit(): Attribute
    {
        return Attribute::get(fn() => match (true) {
            $this?->interval === null => null,
            (bool)$this->interval->d => $this->interval->d % 7 === 0 ? 'w' : 'd',
            (bool)$this->interval->m => 'm',
            (bool)$this->interval->y => 'y',
        });
    }

    public function getKey(): ?string
    {
        return parent::getKey();
    }

    public function intervalCount(): Attribute
    {
        return Attribute::get(fn() => match ($this->interval_unit) {
            null => null,
            'd' => $this->interval->d,
            'w' => $this->interval->d / 7,
            'm' => $this->interval->m,
            'y' => $this->interval->y,
        });
    }

    public function toEntity(): SubscriptionPlanEntity
    {
        return new SubscriptionPlanEntity(
            Uuid::fromString($this->id),
            $this->name,
            $this->description,
            $this->money,
            $this->interval,
            $this->merchant_descriptor,
        );
    }
}
