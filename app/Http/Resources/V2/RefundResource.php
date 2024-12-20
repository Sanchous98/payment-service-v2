<?php

declare(strict_types=1);

namespace App\Http\Resources\V2;

use App\Models\Refund;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Refund
 */
class RefundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->paymentIntent->currency->getCode(),
            'status' => $this->status->value,
            'payment_intent' => PaymentIntentResource::make($this->paymentIntent),
            'created_at' => $this->created_at->format(DateTimeInterface::W3C),
            'updated_at' => $this->updated_at->format(DateTimeInterface::W3C),
        ];
    }
}
