<?php

use App\Http\Controllers\BillingAddressController;
use Illuminate\Support\Facades\Route;

Route::apiResource('billing-addresses', BillingAddressController::class)->whereUuid('billingAddress');
