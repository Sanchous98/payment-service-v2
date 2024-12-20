<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use PaymentSystem\ValueObjects\State;

/**
 * @implements CastsAttributes<State, State>
 */
class StateCast implements CastsAttributes
{
    /**
     * @inheritDoc
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): State|null
    {
        if (!isset($attributes[$key])) {
            return null;
        }

        return new State($attributes[$key]);
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
