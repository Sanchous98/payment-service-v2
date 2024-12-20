<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use PaymentSystem\ValueObjects\PhoneNumber;

/**
 * @implements CastsAttributes<PhoneNumber, PhoneNumber>
 */
class PhoneCast implements CastsAttributes
{
    /**
     * @inheritDoc
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): PhoneNumber|null
    {
        if (!isset($attributes[$key])) {
            return null;
        }

        return new PhoneNumber($attributes[$key]);
    }

    /**
     * @inheritDoc
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return [$key => null];
        }

        return [$key => (string)$value];
    }
}
