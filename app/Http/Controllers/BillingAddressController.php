<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BillingAddress;
use App\Http\Requests\StoreBillingAddress;
use App\Http\Resources\BillingAddressResource;
use Symfony\Component\HttpFoundation\Response;

readonly class BillingAddressController
{
    public function index(): Response
    {
        return BillingAddressResource::collection(BillingAddress::query()->paginate())->response();
    }

    public function store(StoreBillingAddress $request): Response
    {
        return BillingAddressResource::make(BillingAddress::query()->create($request->validated()))->response($request);
    }

    public function show(BillingAddress $billingAddress): Response
    {
        return BillingAddressResource::make($billingAddress)->response();
    }

    public function update(StoreBillingAddress $request, BillingAddress $billingAddress): Response
    {
        return BillingAddressResource::make(tap($billingAddress)->updateOrFail($request->validated()))->response($request);
    }

    public function destroy(BillingAddress $billingAddress): Response
    {
        $billingAddress->delete();

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
