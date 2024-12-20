<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use App\MoonShine\Resources\PaymentIntentResource;
use App\MoonShine\Resources\PaymentMethodResource;
use App\MoonShine\Resources\RefundResource;
use App\MoonShine\Resources\SubscriptionPlanResource;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\MenuManager\MenuItem;
use App\MoonShine\Resources\SubscriptionResource;
use App\MoonShine\Resources\AccountResource;

final class PaymentServiceLayout extends AppLayout
{
    protected function menu(): array
    {
        return [
            ...tap(parent::menu(), fn(array $items) => $items[0]->icon('wrench-screwdriver')),
            MenuItem::make('ui.resource.accounts', AccountResource::class, 'cog')->translatable(),
            MenuItem::make('ui.resource.payment_intents', PaymentIntentResource::class)->translatable(),
            MenuItem::make('ui.resource.refunds', RefundResource::class)->translatable(),
            MenuItem::make('ui.resource.payment_methods', PaymentMethodResource::class)->translatable(),
            MenuItem::make('ui.resource.subscription_plans', SubscriptionPlanResource::class)->translatable(),
            MenuItem::make('ui.resource.subscriptions', SubscriptionResource::class)->translatable(),
        ];
    }

    protected function getFooterCopyright(): string
    {
        return '';
    }

    protected function getFooterMenu(): array
    {
        return [];
    }
}
