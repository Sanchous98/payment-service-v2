<?php

declare(strict_types=1);

namespace App\Http\Resources\V2;

use App\Models\Token;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Token
 */
class TokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'card' => CardResource::make($this->card),
            'used' => $this->used,
            'declined' => !empty($this->decline_reason),
            'decline_reason' => $this->decline_reason,
            'created_at' => $this->created_at->format(DateTimeInterface::W3C),
            'updated_at' => $this->updated_at->format(DateTimeInterface::W3C),
        ];
    }
}
