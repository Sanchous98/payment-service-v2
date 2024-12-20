<?php

use App\Http\Controllers\V2\PaymentIntentController;
use App\Http\Controllers\V2\PaymentMethodController;
use App\Http\Controllers\V2\RefundController;
use App\Http\Controllers\V2\SubscriptionController;
use App\Http\Controllers\V2\SubscriptionPlanController;
use App\Http\Controllers\V2\TokenController;
use Illuminate\Support\Facades\Route;

Route::apiResource('payment-methods', PaymentMethodController::class)->whereUuid('paymentMethod');
Route::apiResource('tokens', TokenController::class)->except('update')->whereUuid('token');
Route::apiResource('payment-intents', PaymentIntentController::class)->whereUuid('paymentIntent');
Route::apiResource('refunds', RefundController::class)->except('update')->whereUuid('refund');
Route::apiResource('subscriptions', SubscriptionController::class)->whereUuid('subscription');
Route::apiResource('subscription-plans', SubscriptionPlanController::class)->only(['index', 'show'])->whereUuid('subscriptionPlan');
