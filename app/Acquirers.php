<?php

namespace App;

enum Acquirers: string
{
    case STRIPE = 'stripe';
    case NUVEI = 'nuvei';
}
