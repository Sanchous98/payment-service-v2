<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\SubscriptionPlan;

use App\Enum\IntervalUnit;
use App\Models\SubscriptionPlan;
use App\MoonShine\Resources\SubscriptionPlanResource;
use ForestLynx\MoonShine\Fields\Decimal;
use MoonShine\Laravel\Pages\Crud\FormPage as BaseFormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use PaymentSystem\ValueObjects\MerchantDescriptor;
use Symfony\Component\Intl\Currencies;


/**
 * @extends BaseFormPage<SubscriptionPlanResource>
 */
class FormPage extends BaseFormPage
{
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Text::make('ui.name', 'name')
                    ->translatable()
                    ->required(),
                Text::make('ui.description', 'description')
                    ->translatable()
                    ->default('')
                    ->required(),
                Flex::make([
                    Decimal::make('ui.price', 'amount')
                        ->unit('currency', array_combine(Currencies::getCurrencyCodes(), Currencies::getCurrencyCodes()))
                        ->required()
                        ->unitDefault('USD')
                        ->unitSearchable()
                        ->translatable(),
                    Decimal::make('ui.interval', 'interval_count')
                        ->required()
                        ->naturalNumber(0)
                        ->unit('interval_unit', IntervalUnit::class)
                        ->unitDefault(IntervalUnit::DAY)
                        ->translatable(),
                ]),
                Text::make('ui.merchant_descriptor', 'merchant_descriptor')
                    ->default('')
                    ->required()
                    ->translatable(),
            ])
        ];
    }
}
