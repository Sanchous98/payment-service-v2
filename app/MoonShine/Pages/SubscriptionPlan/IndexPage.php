<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\SubscriptionPlan;

use App\Enum\IntervalUnit;
use App\MoonShine\Resources\SubscriptionPlanResource;
use ForestLynx\MoonShine\Fields\Decimal;
use MoonShine\Laravel\Pages\Crud\IndexPage as BaseIndexPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use Symfony\Component\Intl\Currencies;


/**
 * @extends BaseIndexPage<SubscriptionPlanResource>
 */
class IndexPage extends BaseIndexPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make(__('ui.name'), 'name')->sortable(),
            Text::make(__('ui.description'), 'description'),
            Decimal::make('ui.price', 'amount')
                ->unit('currency', array_combine(Currencies::getCurrencyCodes(), Currencies::getCurrencyCodes()))
                ->translatable(),
            Decimal::make('ui.interval', 'interval_count')
                ->translatable()
                ->naturalNumber(0)
                ->unit('interval_unit', IntervalUnit::class),
            Text::make(__('ui.merchant_descriptor'), 'merchant_descriptor'),
        ];
    }
}
