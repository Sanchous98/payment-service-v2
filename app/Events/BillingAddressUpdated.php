<?php

namespace App\Events;

use App\Models\BillingAddress;

readonly class BillingAddressUpdated
{
    public function __construct(public BillingAddress $billingAddress)
    {
    }
}
