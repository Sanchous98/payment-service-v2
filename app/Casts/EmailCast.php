<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use PaymentSystem\ValueObjects\Email;

/**
 * @implements CastsAttributes<Email, Email>
 */
class EmailCast implements CastsAttributes
{
    /**
     * @inheritDoc
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Email|null
    {
        if (!isset($attributes[$key])) {
            return null;
        }

        return new Email($attributes[$key]);
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
