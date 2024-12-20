<?php

declare(strict_types=1);

namespace App\Http\Resources\V2;

use App\Models\Card;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Card
 */
class CardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first6' => $this->first6,
            'last4' => $this->last4,
            'brand' => $this->brand,
            'expiration_month' => $this->expiration_month,
            'expiration_year' => $this->expiration_year,
            'holder' => $this->holder,
            'created_at' => $this->created_at->format(DateTimeInterface::W3C),
            'updated_at' => $this->updated_at->format(DateTimeInterface::W3C),
        ];
    }
}
