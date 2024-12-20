<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\ValueObjectCast;
use App\Events\BillingAddressUpdated;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PaymentSystem\ValueObjects\Country;
use PaymentSystem\ValueObjects\Email;
use PaymentSystem\ValueObjects\PhoneNumber;
use PaymentSystem\ValueObjects\State;

class BillingAddress extends Model
{
    use HasUuids;

    protected $dispatchesEvents = [
        'updated' => BillingAddressUpdated::class,
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'city',
        'state',
        'country',
        'postal_code',
        'phone',
        'email',
        'address_line',
        'address_line_extra',
    ];

    protected $casts = [
        'country' => ValueObjectCast::class . ':' . Country::class,
        'state' => ValueObjectCast::class . ':' . State::class,
        'email' => ValueObjectCast::class . ':' . Email::class,
        'phone' => ValueObjectCast::class . ':' . PhoneNumber::class,
    ];

    public static function fromValueObject(
        \PaymentSystem\ValueObjects\BillingAddress $address,
        DateTimeInterface $date
    ) {
        return self::unguarded(fn() => new self([
            'first_name' => $address->firstName,
            'last_name' => $address->lastName,
            'city' => $address->city,
            'country' => $address->country,
            'postal_code' => $address->postalCode,
            'email' => $address->email,
            'phone' => $address->phone,
            'address_line' => $address->addressLine,
            'address_line_extra' => $address->addressLineExtra,
            'state' => $address->state ?? '',
            'created_at' => $date,
            'updated_at' => $date,
        ]));
    }

    public function toValueObject(): \PaymentSystem\ValueObjects\BillingAddress
    {
        return new \PaymentSystem\ValueObjects\BillingAddress(
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

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }
}
