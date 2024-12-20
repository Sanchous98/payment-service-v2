<?php

declare(strict_types=1);

namespace App\Http\Resources\V2;

use App\Models\PaymentIntent;
use App\Models\PaymentMethod;
use App\Models\Token;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PaymentIntent
 */
class PaymentIntentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'currency' => $this->money->getCurrency()->getCode(),
            'amount' => $this->money->getAmount(),
            'fee_currency' => $this->fee?->getCurrency()->getCode(),
            'fee_amount' => $this->fee?->getAmount(),
            'status' => $this->status->value,
            'token' => $this->when($this->tender instanceof Token, fn() => TokenResource::make($this->tender, 'token', $this->tender->id)),
            'payment_method' => $this->when($this->tender instanceof PaymentMethod, fn() => PaymentMethodResource::make($this->tender, 'payment_method', $this->tender->id)),
            'merchant_descriptor' => $this->merchant_descriptor,
            'description' => $this->description,
            'created_at' => $this->created_at->format(DateTimeInterface::W3C),
            'updated_at' => $this->updated_at->format(DateTimeInterface::W3C),
        ];
    }
}
