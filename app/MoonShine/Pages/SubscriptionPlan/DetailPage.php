<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\SubscriptionPlan;

use App\Enum\IntervalUnit;
use App\MoonShine\Resources\SubscriptionPlanResource;
use ForestLynx\MoonShine\Fields\Decimal;
use MoonShine\Laravel\Pages\Crud\DetailPage as BaseDetailPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use Symfony\Component\Intl\Currencies;


/**
 * @extends BaseDetailPage<SubscriptionPlanResource>
 */
class DetailPage extends BaseDetailPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('ui.name', 'name')->translatable(),
            Text::make('ui.description', 'description')->translatable(),
            Decimal::make('ui.price', 'amount')
                ->unit('currency', array_combine(Currencies::getCurrencyCodes(), Currencies::getCurrencyCodes()))
                ->translatable(),
            Decimal::make('ui.interval', 'interval_count')
                ->translatable()
                ->naturalNumber(0)
                ->unit('interval_unit', IntervalUnit::class),
            Text::make('ui.merchant_descriptor', 'merchant_descriptor')->translatable(),
        ];
    }
}
