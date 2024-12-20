<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Subscription;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;

/**
 * @extends ModelResource<Subscription>
 */
#[Icon('newspaper')]
class SubscriptionResource extends ModelResource
{
    protected string $model = Subscription::class;

    protected string $title { get => __('ui.resource.subscriptions'); }

    protected bool $detailInModal = true;

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->only(Action::VIEW);
    }

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
//            BelongsTo::make(__('ui.resource.accounts'), 'account'),
//            BelongsTo::make(__('ui.resource.subscription_plans'), 'subscriptionPlan'),
//            BelongsTo::make(__('ui.resource.payment_methods'), 'paymentMethod'),
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            ID::make(),
//            BelongsTo::make(__('ui.resource.accounts'), 'account'),
//            BelongsTo::make(__('ui.resource.subscription_plans'), 'subscriptionPlan'),
//            BelongsTo::make(__('ui.resource.payment_methods'), 'paymentMethod'),
        ];
    }
}
