<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBillingAddress;
use App\Http\Resources\BillingAddressResource;
use App\Models\BillingAddress;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BillingAddressController
{
    public function index(): JsonResponse
    {
        return BillingAddressResource::collection(BillingAddress::query()->paginate())->response();
    }

    public function store(StoreBillingAddress $request): JsonResponse
    {
        return BillingAddressResource::make(BillingAddress::query()->create($request->validated()))->response($request);
    }

    public function show(BillingAddress $billingAddress): JsonResponse
    {
        return BillingAddressResource::make($billingAddress)->response();
    }

    public function update(StoreBillingAddress $request, BillingAddress $billingAddress): JsonResponse
    {
        return BillingAddressResource::make(tap($billingAddress)->updateOrFail($request->validated()))->response($request);
    }

    public function destroy(BillingAddress $billingAddress): JsonResponse
    {
        $billingAddress->delete();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
