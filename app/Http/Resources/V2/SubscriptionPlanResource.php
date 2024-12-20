<?php

namespace App\Http\Resources\V2;

use App\Models\SubscriptionPlan;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SubscriptionPlan
 */
class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'amount' => $this->amount,
            'currency' => (string)$this->currency,
            'interval_unit' => $this->interval_unit,
            'interval_count' => $this->interval_count,
            'merchant_descriptor' => $this->merchant_descriptor,
            'created_at' => $this->created_at->format(DateTimeInterface::W3C),
            'updated_at' => $this->updated_at->format(DateTimeInterface::W3C),
        ];
    }
}
