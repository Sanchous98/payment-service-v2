<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\ValueObjectCast;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use PaymentSystem\Laravel\Uuid;
use PaymentSystem\ValueObjects\Country;
use PaymentSystem\ValueObjects\Email;
use PaymentSystem\ValueObjects\PhoneNumber;
use PaymentSystem\ValueObjects\State;
use PaymentSystem\Entities\BillingAddress as BillingAddressEntity;

class BillingAddress extends Model
{
    use HasUuids;

    protected $guarded = ['*'];

    protected $casts = [
        'id' => Uuid::class,
        'country' => ValueObjectCast::class . ':' . Country::class,
        'state' => ValueObjectCast::class . ':' . State::class,
        'email' => ValueObjectCast::class . ':' . Email::class,
        'phone' => ValueObjectCast::class . ':' . PhoneNumber::class,
    ];

    public function toEntity(): BillingAddressEntity
    {
        return new BillingAddressEntity(
            $this->id,
            $this->first_name,
            $this->last_name,
            $this->city,
            $this->country,
            $this->postal_code,
            $this->email,
            $this->phone,
            $this->address_line,
            $this->address_line_extra,
            $this->state,
        );
    }
}
