<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\EncryptAwareInterface;
use App\Enum\Acquirers;
use App\Listeners;
use App\Models\Card;
use App\Models\PaymentMethod;
use App\Models\Token;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MoonShine\Laravel\Models\MoonshineUser;
use PaymentSystem\Contracts\EncryptInterface;
use PaymentSystem\Laravel\Nuvei\Models\Credentials as NuveiCredentials;
use PaymentSystem\Laravel\Stripe\Models\Credentials as StripeCredentials;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->resolving(EncryptAwareInterface::class, function (EncryptAwareInterface $object, Container $container) {
            $object->setEncrypt($container->get(EncryptInterface::class));
        });
    }

    public function boot(Dispatcher $events): void
    {
        $events->subscribe(Listeners\TokenModelSubscriber::class);
        $events->subscribe(Listeners\PaymentMethodModelSubscriber::class);
        $events->subscribe(Listeners\PaymentIntentModelSubscriber::class);
        $events->subscribe(Listeners\RefundModelSubscriber::class);
        $events->subscribe(Listeners\BillingAddressModelSubscriber::class);
        $events->subscribe(Listeners\SubscriptionModelSubscriber::class);
        $events->subscribe(Listeners\SubscriptionPlanModelSubscriber::class);

        Relation::requireMorphMap();
        Relation::morphMap([
            'card' => Card::class,
            'token' => Token::class,
            'payment_method' => PaymentMethod::class,
            Acquirers::STRIPE->value  => StripeCredentials::class,
            Acquirers::NUVEI->value => NuveiCredentials::class,
            'moonshine_user' => MoonshineUser::class,
        ]);
    }
}
