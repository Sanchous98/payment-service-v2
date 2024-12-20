<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BillingAddress;
use App\Http\Requests\StoreBillingAddress;
use App\Http\Resources\BillingAddressResource;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Str;
use PaymentSystem\Laravel\Jobs\CreateBillingAddressJob;
use PaymentSystem\Laravel\Jobs\DeleteBillingAddressJob;
use PaymentSystem\Laravel\Jobs\UpdateBillingAddressJob;
use PaymentSystem\Laravel\Uuid;
use PaymentSystem\Repositories\SubscriptionPlanRepositoryInterface;
use PaymentSystem\ValueObjects\Country;
use PaymentSystem\ValueObjects\Email;
use PaymentSystem\ValueObjects\PhoneNumber;
use PaymentSystem\ValueObjects\State;
use Symfony\Component\HttpFoundation\Response;

readonly class BillingAddressController
{
    public function __construct(private Dispatcher $dispatcher)
    {
    }

    public function index(): Response
    {
        return BillingAddressResource::collection(BillingAddress::query()->paginate())->response();
    }

    public function store(StoreBillingAddress $request): Response
    {
        $this->dispatcher->dispatchSync(new CreateBillingAddressJob(
            $id = new Uuid(Str::uuid7()),
            (string)$request->safe()->str('first_name'),
            (string)$request->safe()->str('last_name'),
            (string)$request->safe()->str('city'),
            new Country((string)$request->safe()->str('country')),
            (string)$request->safe()->str('postal_code'),
            new Email((string)$request->safe()->str('email')),
            new PhoneNumber((string)$request->safe()->str('phone')),
            (string)$request->safe()->str('address_line'),
            (string)$request->safe()->str('address_line_extra'),
            $request->safe()->has('state') ? new State((string)$request->safe()->str('state')) : null,
        ));

        return BillingAddressResource::make(BillingAddress::query()->findOrFail($id))->response($request);
    }

    public function show(BillingAddress $billingAddress): Response
    {
        return BillingAddressResource::make($billingAddress)->response();
    }

    public function update(StoreBillingAddress $request, BillingAddress $billingAddress): Response
    {
        $this->dispatcher->dispatchSync(new UpdateBillingAddressJob(
            Uuid::fromString($billingAddress->id),
            (string)$request->safe()->input('first_name'),
            (string)$request->safe()->input('last_name'),
            (string)$request->safe()->input('city'),
            $request->safe()->has('country') ? new Country((string)$request->safe()->str('country')) : null,
            (string)$request->safe()->input('postal_code'),
            $request->safe()->has('email') ? new Email((string)$request->safe()->str('email')) : null,
            $request->safe()->has('phone') ? new PhoneNumber((string)$request->safe()->str('phone')) : null,
            (string)$request->safe()->input('address_line'),
            (string)$request->safe()->input('address_line_extra'),
            $request->safe()->has('state') ? new State((string)$request->safe()->str('state')) : null,
        ));

        return BillingAddressResource::make($billingAddress->fresh())->response($request);
    }

    public function destroy(BillingAddress $billingAddress): Response
    {
        $this->dispatcher->dispatchSync(new DeleteBillingAddressJob($billingAddress->id));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
