<?php

namespace App\Casts;

use DateInterval;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements CastsAttributes<DateInterval, DateInterval>
 */
class DateIntervalCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?DateInterval
    {
        if ($value === null) {
            return null;
        }

        return new DateInterval($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        assert($value instanceof DateInterval);

        return $value->format('P%yY%mM%dD');
    }
}
