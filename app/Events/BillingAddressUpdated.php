<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\BillingAddress;

readonly class BillingAddressUpdated
{
    public function __construct(public BillingAddress $billingAddress)
    {
    }
}
