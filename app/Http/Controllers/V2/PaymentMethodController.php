<?php

declare(strict_types=1);

namespace App\Http\Controllers\V2;

use App\Http\Requests\V2\StorePaymentMethodRequest;
use App\Http\Requests\V2\UpdatePaymentMethodRequest;
use App\Http\Resources\V2\PaymentMethodResource;
use App\Models\PaymentMethod;
use Illuminate\Contracts\Bus\Dispatcher;
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

    public function index(): Response
    {
        return PaymentMethodResource::collection(PaymentMethod::query()->paginate())->response();
    }

    public function store(StorePaymentMethodRequest $request): Response
    {
        $id = new Uuid(Str::uuid7());

        if ($request->has('token_id')) {
            $this->dispatcher->dispatchSync(new CreateTokenPaymentMethodJob(
                $id,
                $this->repository->retrieve(Uuid::fromString((string)$request->str('token_id'))),
            ));
        } else {
            $this->dispatcher->dispatchSync(new CreatePaymentMethodJob(
                $id,
                $request->billingAddress()->toEntity(),
                $request->source(),
            ));
        }


        return PaymentMethodResource::make(PaymentMethod::query()->find($id))->response($request);
    }

    public function show(PaymentMethod $paymentMethod): Response
    {
        return PaymentMethodResource::make($paymentMethod)->response();
    }

    public function update(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod): Response
    {
        $this->dispatcher->dispatchSync(new UpdatePaymentMethodJob(
            Uuid::fromString($paymentMethod->id),
            $request->billingAddress()->toEntity(),
        ));

        return PaymentMethodResource::make($paymentMethod->fresh())->response($request);
    }

    public function destroy(PaymentMethod $paymentMethod): Response
    {
        $this->dispatcher->dispatchSync(new SuspendPaymentMethodJob(Uuid::fromString($paymentMethod->id)));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
