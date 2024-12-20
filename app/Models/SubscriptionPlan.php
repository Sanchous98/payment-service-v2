<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Casts\ValueObjectCast;
use App\Enum\IntervalUnit;
use DateInterval;
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
        'interval_unit' => IntervalUnit::class,
        'money' => MoneyCast::class,
        'currency' => ValueObjectCast::class . ':' . Currency::class,
        'merchant_descriptor' => ValueObjectCast::class . ':' . MerchantDescriptor::class,
    ];

    public function getKey(): ?string
    {
        return parent::getKey();
    }

    public function interval(): Attribute
    {
        return Attribute::make(
            fn() => match ($this->interval_unit) {
                IntervalUnit::DAY => new DateInterval("P{$this->interval_count}D"),
                IntervalUnit::WEEK => new DateInterval("P{$this->interval_count}W"),
                IntervalUnit::MONTH => new DateInterval("P{$this->interval_count}M"),
                IntervalUnit::YEAR => new DateInterval("P{$this->interval_count}Y"),
                null => null,
            },
            fn(?DateInterval $interval) => match (true) {
                $interval === null => null,
                (bool)$interval->d => $interval->d % 7 === 0 ? ['interval_unit' => IntervalUnit::WEEK, 'interval_count' => $interval->d / 7] : ['interval_unit' => IntervalUnit::DAY, 'interval_count' => $interval->d],
                (bool)$interval->m => ['interval_unit' => IntervalUnit::MONTH, 'interval_count' => $interval->m],
                (bool)$interval->y => ['interval_unit' => IntervalUnit::YEAR, 'interval_count' => $interval->y],
            }
        );
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
