<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use App\MoonShine\Resources\StripeResource;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;
use App\MoonShine\Resources\NuveiResource;
use App\MoonShine\Resources\PaymentIntentResource;
use App\MoonShine\Resources\RefundResource;
use App\MoonShine\Resources\PaymentMethodResource;

final class KivPayLayout extends AppLayout
{
    protected function menu(): array
    {
        return [
            ...parent::menu(),
            MenuGroup::make(static fn () => __('ui.integrations'), [
                MenuItem::make('Stripe', StripeResource::class),
                MenuItem::make('Nuvei', NuveiResource::class),
            ]),
            MenuItem::make('Payment Intents', PaymentIntentResource::class),
            MenuItem::make('Refunds', RefundResource::class),
            MenuItem::make('Payment Methods', PaymentMethodResource::class),
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
