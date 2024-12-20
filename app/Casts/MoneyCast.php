<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Money\Currency;
use Money\Money;

/**
 * @implements CastsAttributes<Money, Money>
 */
readonly class MoneyCast implements CastsAttributes
{
    public function __construct(private string $prefix = '')
    {
    }

    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        if (!isset($attributes["{$this->prefix}amount"], $attributes["{$this->prefix}currency"])) {
            return null;
        }

        return new Money($attributes["{$this->prefix}amount"], new Currency($attributes["{$this->prefix}currency"]));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if ($value === null) {
            return [
                'amount' => null,
                'currency' => null,
            ];
        }

        assert($value instanceof Money);

        return [
            "{$this->prefix}amount" => $value->getAmount(),
            "{$this->prefix}currency" => $value->getCurrency()->getCode(),
        ];
    }
}
