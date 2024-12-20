<?php

declare(strict_types=1);

namespace App\Http\Resources\V2;

use App\Http\Resources\AccountResource;
use App\Models\PaymentMethod;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use PaymentSystem\Enum\PaymentMethodStatusEnum;

/**
 * @mixin PaymentMethod
 */
class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'billing_address_id' => $this->billingAddress->id,
            'type' => $this->source_type,
            $this->source_type => match ($this->source_type){
                'card' => CardResource::make($this->source, 'card', $this->source->id),
                default => throw new \RuntimeException('invalid source type'),
            },
            'available_accounts' => $this->when($this->status === PaymentMethodStatusEnum::SUCCEEDED, fn() => AccountResource::collection($this->accounts)),
            'status' => $this->status->value,
            'created_at' => $this->created_at->format(DateTimeInterface::W3C),
            'updated_at' => $this->updated_at->format(DateTimeInterface::W3C),
        ];
    }
}
