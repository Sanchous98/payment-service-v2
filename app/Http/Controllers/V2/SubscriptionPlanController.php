<?php

namespace App\Http\Controllers\V2;

use App\Http\Resources\V2\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use Symfony\Component\HttpFoundation\Response;

readonly class SubscriptionPlanController
{
    public function index(): Response
    {
        return SubscriptionPlanResource::collection(SubscriptionPlan::all())->response();
    }

    public function show(SubscriptionPlan $subscriptionPlan): Response
    {
        return SubscriptionPlanResource::make($subscriptionPlan)->response();
    }
}
