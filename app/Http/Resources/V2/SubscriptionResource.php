<?php

namespace App\Http\Resources\V2;

use App\Http\Resources\AccountResource;
use App\Models\Subscription;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Subscription
 */
class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'account' => AccountResource::make($this->account),
            'subscription_plan' => SubscriptionPlanResource::make($this->subscriptionPlan),
            'payment_method' => PaymentMethodResource::make($this->paymentMethod),
            'ends_at' => $this->ends_at->format('Y-m-d'),
            'created_at' => $this->created_at->format(DateTimeInterface::W3C),
            'updated_at' => $this->updated_at->format(DateTimeInterface::W3C),
        ];
    }
}
