<?php

declare(strict_types=1);

namespace App\Http\Controllers\V2;

use App\Http\Requests\V2\StoreRefundRequest;
use App\Http\Resources\V2\RefundResource;
use App\Models\Refund;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Str;
use Money\Money;
use PaymentSystem\Laravel\Jobs\CancelRefundJob;
use PaymentSystem\Laravel\Jobs\CreateRefundJob;
use PaymentSystem\Laravel\Uuid;
use PaymentSystem\Repositories\PaymentIntentRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

readonly class RefundController
{
    public function __construct(
        private Dispatcher $dispatcher,
        private PaymentIntentRepositoryInterface $repository,
    ) {
    }

    public function index(): Response
    {
        return RefundResource::collection(Refund::query()->paginate())->response();
    }

    public function store(StoreRefundRequest $request): Response
    {
        $this->dispatcher->dispatchSync(new CreateRefundJob(
            $id = new Uuid(Str::uuid7()),
            $this->repository->retrieve(Uuid::fromString($request->paymentIntent()->id)),
            new Money((string)$request->str('amount'), $request->paymentIntent()->currency),
            $request->paymentIntent()->account,
        ));

        return RefundResource::make(Refund::query()->findOrFail($id))->response($request);
    }

    public function show(Refund $refund): Response
    {
        return RefundResource::make($refund)->response();
    }

    public function destroy(Refund $refund): Response
    {
        $this->dispatcher->dispatchSync(new CancelRefundJob(
            Uuid::fromString($refund->id),
            $refund->paymentIntent->account,
        ));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
