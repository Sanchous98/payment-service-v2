<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Stringable;

/**
 * @template T of Stringable
 * @implements CastsAttributes<T, T>
 */
readonly class ValueObjectCast implements CastsAttributes
{
    /**
     * @param class-string<T> $valueObjectClass
     */
    public function __construct(private string $valueObjectClass)
    {
    }

    /**
     * @return T|null
     */
    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        if (!isset($attributes[$key])) {
            return null;
        }

        return new ($this->valueObjectClass)($attributes[$key]);
    }

    /**
     * @param T|null $value
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return [$key => null];
        }

        return [$key => (string)$value];
    }
}
