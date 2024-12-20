<?php

declare(strict_types=1);

namespace App\Http\Controllers\V2;

use App\Http\Requests\V2\StoreSubscriptionRequest;
use App\Http\Requests\V2\UpdateSubscriptionRequest;
use App\Http\Resources\V2\SubscriptionResource;
use App\Models\Subscription;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Str;
use PaymentSystem\Laravel\Jobs\SubscriptionCancelJob;
use PaymentSystem\Laravel\Jobs\SubscriptionCreateJob;
use PaymentSystem\Laravel\Jobs\SubscriptionUpdatePaymentMethodJob;
use PaymentSystem\Laravel\Uuid;
use PaymentSystem\Repositories\PaymentMethodRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

readonly class SubscriptionController
{
    public function __construct(private Dispatcher $dispatcher, private PaymentMethodRepositoryInterface $repository)
    {
    }

    public function index(): Response
    {
        return SubscriptionResource::collection(Subscription::query()->paginate())->response();
    }

    public function store(StoreSubscriptionRequest $request): Response
    {
        $this->dispatcher->dispatchSync(new SubscriptionCreateJob(
            $id = new Uuid(Str::uuid7()),
            $request->subscriptionPlan()->toEntity(),
            $this->repository->retrieve(Uuid::fromString((string)$request->str('payment_method_id'))),
            $request->account(),
        ));

        return SubscriptionResource::make(Subscription::query()->findOrFail($id))->response($request);
    }

    public function show(Subscription $subscription): Response
    {
        return SubscriptionResource::make($subscription)->response();
    }

    public function update(UpdateSubscriptionRequest $request, Subscription $subscription): Response
    {
        $this->dispatcher->dispatchSync(new SubscriptionUpdatePaymentMethodJob(
            Uuid::fromString($subscription->id),
            $subscription->account,
            $this->repository->retrieve(Uuid::fromString((string)$request->str('payment_method_id'))),
        ));

        return SubscriptionResource::make(tap($subscription)->update($request->validated()))->response($request);
    }

    public function destroy(Subscription $subscription): Response
    {
        $this->dispatcher->dispatchSync(new SubscriptionCancelJob($subscription->id, $subscription->account));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
