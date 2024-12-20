<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Resources\MoonShineUserResource;
use App\MoonShine\Resources\MoonShineUserRoleResource;
use App\MoonShine\Resources\PaymentIntentResource;
use App\MoonShine\Resources\PaymentMethodResource;
use App\MoonShine\Resources\RefundResource;
use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\ConfiguratorContract;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;
use App\MoonShine\Resources\SubscriptionPlanResource;
use App\MoonShine\Resources\SubscriptionResource;
use App\MoonShine\Resources\AccountResource;

class MoonShineServiceProvider extends ServiceProvider
{
    /**
     * @param  MoonShine  $core
     * @param  MoonShineConfigurator  $config
     */
    public function boot(CoreContract $core, ConfiguratorContract $config): void
    {
        // $config->authEnable();

        $core
            ->resources([
                MoonShineUserResource::class,
                MoonShineUserRoleResource::class,
                PaymentIntentResource::class,
                RefundResource::class,
                PaymentMethodResource::class,
                SubscriptionPlanResource::class,
                SubscriptionResource::class,
                AccountResource::class,
            ])
            ->pages([
                ...$config->getPages(),
            ])
        ;
    }
}
