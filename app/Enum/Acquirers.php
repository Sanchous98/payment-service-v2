<?php

namespace App\Enum;

enum Acquirers: string
{
    case STRIPE = 'stripe';
    case NUVEI = 'nuvei';

    public function toString(): string
    {
        return match($this) {
            self::STRIPE => 'Stripe',
            self::NUVEI => 'Nuvei',
        };
    }
}
