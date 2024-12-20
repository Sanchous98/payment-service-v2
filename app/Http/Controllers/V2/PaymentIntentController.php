<?php

namespace App\Http\Controllers\V2;

use App\Http\Requests\V2\StorePaymentIntentRequest;
use App\Http\Requests\V2\UpdatePaymentIntentRequest;
use App\Http\Resources\V2\PaymentIntentResource;
use App\Models\PaymentIntent;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use PaymentSystem\Laravel\Jobs\AuthorizePaymentIntentJob;
use PaymentSystem\Laravel\Jobs\CancelPaymentIntentJob;
use PaymentSystem\Laravel\Jobs\CapturePaymentIntentJob;
use PaymentSystem\Laravel\Uuid;
use PaymentSystem\Repositories\TenderRepositoryInterface;

readonly class PaymentIntentController
{
    public function __construct(
        private TenderRepositoryInterface $tenderRepository,
        private Dispatcher $dispatcher,
    ) {
    }

    public function index(): JsonResponse
    {
        return PaymentIntentResource::collection(PaymentIntent::query()->paginate())->response();
    }

    public function store(StorePaymentIntentRequest $request): JsonResponse
    {
        $this->dispatcher->dispatchSync(new AuthorizePaymentIntentJob(
            $id = new Uuid(Str::orderedUuid()),
            $request->money(),
            $request->account(),
            $this->tenderRepository->retrieve($request->tenderId()),
            $request->str('merchant_descriptor'),
            $request->str('description'),
            $request->threeDS(),
        ));

        return PaymentIntentResource::make(PaymentIntent::query()->findOrFail($id))->response($request);
    }

    public function show(PaymentIntent $paymentIntent): JsonResponse
    {
        return PaymentIntentResource::make($paymentIntent)->response();
    }

    public function update(UpdatePaymentIntentRequest $request, PaymentIntent $paymentIntent): JsonResponse
    {
        $this->dispatcher->dispatchSync(new CapturePaymentIntentJob(
            Uuid::fromString($paymentIntent->id),
            $paymentIntent->account,
            $request->input('amount') ?? null,
        ));

        return PaymentIntentResource::make($paymentIntent->fresh())->response($request);
    }

    public function destroy(PaymentIntent $paymentIntent): JsonResponse
    {
        $this->dispatcher->dispatchSync(new CancelPaymentIntentJob(Uuid::fromString($paymentIntent->id), $paymentIntent->account));

        return PaymentIntentResource::make($paymentIntent->fresh())->response();
    }
}
