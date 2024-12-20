<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use PaymentSystem\Laravel\Models\Account;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated
 */
readonly class AccountController
{
    public function index(): Response
    {
        $accounts = QueryBuilder::for(Account::class)
            ->allowedFilters([
                AllowedFilter::partial('description'),
                AllowedFilter::exact('external_id'),
                AllowedFilter::exact('credentials_type'),
            ])
            ->paginate();

        return AccountResource::collection($accounts)->response();
    }


    public function store(StoreAccountRequest $request): Response
    {
        $account = tap(new Account($request->validated()), function (Account $account) use ($request) {
            $account->credentials()->associate($request->account());
            $account->push();
        });

        return AccountResource::make($account)->response($request);
    }

    public function show(Account $account)
    {
        return AccountResource::make($account)->response();
    }

    public function update(UpdateAccountRequest $request, Account $account)
    {
        $account->update($request->validated());
        $account->credentials->update($request->credentials());

        return AccountResource::make($account)->response($request);
    }

    public function destroy(Account $account): Response
    {
        $account->credentials()->delete();
        $account->delete();

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
