<?php

namespace App\Providers;

use App\Contracts\EncryptAwareInterface;
use App\Listeners;
use App\Models\Card;
use App\Models\PaymentMethod;
use App\Models\Token;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MoonShine\Laravel\Models\MoonshineUser;
use PaymentSystem\Contracts\EncryptInterface;
use PaymentSystem\Laravel\Stripe\Models\Credentials as StripeCredentials;
use PaymentSystem\Laravel\Nuvei\Models\Credentials as NuveiCredentials;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Listeners\CancelPaymentIntentModelListener::class);
        $this->app->singleton(Listeners\CancelRefundModelListener::class);
        $this->app->singleton(Listeners\CapturePaymentIntentModelListener::class);
        $this->app->singleton(Listeners\CreatePaymentIntentModelListener::class);
        $this->app->singleton(Listeners\CreatePaymentMethodModelListener::class);
        $this->app->singleton(Listeners\CreateRefundModelListener::class);
        $this->app->singleton(Listeners\CreateTokenModelListener::class);
        $this->app->singleton(Listeners\DeclineRefundModelListener::class);
        $this->app->singleton(Listeners\FailPaymentMethodModelListener::class);
        $this->app->singleton(Listeners\SucceedPaymentMethodModelListener::class);
        $this->app->singleton(Listeners\SucceedRefundModelListener::class);
        $this->app->singleton(Listeners\SuspendPaymentMethodModelListener::class);
        $this->app->singleton(Listeners\UseTokenModelListener::class);

        $this->app->resolving(EncryptAwareInterface::class, function (EncryptAwareInterface $object, Container $container) {
            $object->setEncrypt($container->get(EncryptInterface::class));
        });
    }

    public function boot(): void
    {
        Relation::requireMorphMap();
        Relation::morphMap([
            'card' => Card::class,
            'token' => Token::class,
            'payment_method' => PaymentMethod::class,
            'stripe' => StripeCredentials::class,
            'nuvei' => NuveiCredentials::class,
            'moonshine_user' => MoonshineUser::class,
        ]);
    }
}
