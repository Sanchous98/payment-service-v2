<?php

namespace App\Http\Controllers\V2;

use App\Http\Requests\V2\StorePaymentMethodRequest;
use App\Http\Requests\V2\UpdatePaymentMethodRequest;
use App\Http\Resources\V2\PaymentMethodResource;
use App\Models\PaymentMethod;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use PaymentSystem\Laravel\Jobs\CreatePaymentMethodJob;
use PaymentSystem\Laravel\Jobs\CreateTokenPaymentMethodJob;
use PaymentSystem\Laravel\Jobs\SuspendPaymentMethodJob;
use PaymentSystem\Laravel\Jobs\UpdatePaymentMethodJob;
use PaymentSystem\Laravel\Uuid;
use PaymentSystem\Repositories\TokenRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

readonly class PaymentMethodController
{
    public function __construct(
        private Dispatcher $dispatcher,
        private TokenRepositoryInterface $repository,
    ) {
    }

    public function index(): JsonResponse
    {
        return PaymentMethodResource::collection(PaymentMethod::query()->paginate())->response();
    }

    public function store(StorePaymentMethodRequest $request): JsonResponse
    {
        $id = new Uuid(Str::orderedUuid());

        if ($request->has('token_id')) {
            $this->dispatcher->dispatchSync(new CreateTokenPaymentMethodJob(
                $id,
                $request->billingAddress()->toValueObject(),
                $this->repository->retrieve(Uuid::fromString($request->str('token_id'))),
            ));
        } else {
            $this->dispatcher->dispatchSync(new CreatePaymentMethodJob(
                $id,
                $request->billingAddress()->toValueObject(),
                $request->source(),
            ));
        }


        return PaymentMethodResource::make(PaymentMethod::query()->find($id))->response($request);
    }

    public function show(PaymentMethod $paymentMethod): JsonResponse
    {
        return PaymentMethodResource::make($paymentMethod)->response();
    }

    public function update(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $this->dispatcher->dispatchSync(new UpdatePaymentMethodJob(
            Uuid::fromString($paymentMethod->id),
            $request->billingAddress()->toValueObject(),
        ));

        return PaymentMethodResource::make($paymentMethod->fresh())->response($request);
    }

    public function destroy(PaymentMethod $paymentMethod): JsonResponse
    {
        $this->dispatcher->dispatchSync(new SuspendPaymentMethodJob(Uuid::fromString($paymentMethod->id)));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
