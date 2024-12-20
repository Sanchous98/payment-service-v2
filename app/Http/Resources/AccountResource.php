<?php

namespace App\Http\Resources;

use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use PaymentSystem\Laravel\Models\Account;

/**
 * @deprecated
 * @mixin Account
 */
class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->credentials_type,
            'description' => $this->description,
            'external_id' => $this->external_id,
            'created_at' => $this->created_at->format(DateTimeInterface::W3C),
            'updated_at' => $this->updated_at->format(DateTimeInterface::W3C),
        ];
    }
}
