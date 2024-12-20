<?php

namespace App\Http\Resources;

use App\Models\BillingAddress;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BillingAddress
 */
class BillingAddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'address_line' => $this->address_line,
            'address_line_extra' => $this->when($this->address_line_extra !== '', $this->address_line_extra),
            'city' => $this->city,
            'state' => $this->when(!empty($this->state), $this->state),
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'email' => $this->email,
            'phone' => $this->phone,
            'created_at' => $this->created_at->format(DateTimeInterface::W3C),
            'updated_at' => $this->updated_at->format(DateTimeInterface::W3C),
        ];
    }
}
