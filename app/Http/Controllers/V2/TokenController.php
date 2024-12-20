<?php

namespace App\Http\Controllers\V2;

use App\Http\Requests\V2\StoreTokenRequest;
use App\Http\Resources\V2\TokenResource;
use App\Models\Token;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use PaymentSystem\Laravel\Jobs\CreateTokenJob;
use PaymentSystem\Laravel\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

readonly class TokenController
{
    public function __construct(private Dispatcher $dispatcher)
    {
    }

    public function index(): JsonResponse
    {
        return TokenResource::collection(Token::query()->paginate())->response();
    }

    public function store(StoreTokenRequest $request): JsonResponse
    {
        $this->dispatcher->dispatchSync(new CreateTokenJob(
            $id = new Uuid(Str::orderedUuid()),
            $request->source(),
        ));

        return TokenResource::make(Token::query()->findOrFail($id))->response($request);
    }

    public function show(Token $token): JsonResponse
    {
        return TokenResource::make($token)->response();
    }

    /**
     * @todo Revoke token
     */
    public function destroy(Token $token): JsonResponse
    {
        throw new HttpException(Response::HTTP_NOT_IMPLEMENTED);
    }
}
