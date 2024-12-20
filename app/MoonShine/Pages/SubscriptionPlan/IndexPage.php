<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\SubscriptionPlan;

use App\Models\SubscriptionPlan;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use Money\MoneyFormatter;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\Pages\Crud\IndexPage as BaseIndexPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Template;
use MoonShine\UI\Fields\Text;


/**
 * @extends BaseIndexPage<ModelResource>
 */
class IndexPage extends BaseIndexPage
{
    private readonly MoneyFormatter $formatter;

    public function __construct(CoreContract $core)
    {
        parent::__construct($core);

        $this->formatter = new IntlMoneyFormatter(new \NumberFormatter('en-US', \NumberFormatter::CURRENCY), new ISOCurrencies());
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make(__('ui.name'), 'name')->sortable(),
            Text::make(__('ui.description'), 'description'),
            Template::make(__('ui.price'), 'money')
                ->changeFill(fn(SubscriptionPlan $data) => $data->money)
                ->changeRender(fn(Money $value, Template $ctx) => $this->formatter->format($value)),
            Template::make(__('ui.interval'), 'interval')
                ->changeFill(fn(mixed $data) => data_get($data, 'interval'))
                ->changeRender(fn(\DateInterval $value, Template $ctx) => match(true) {
                    (bool)$value->d => $value->d % 7 === 0 ? trans_choice('interval.weeks', $value->d / 7, ['value' => $value->d / 7]) : trans_choice('interval.days', $value->d, ['value' => $value->d]),
                    (bool)$value->m => trans_choice('interval.months', $value->m, ['value' => $value->m]),
                    (bool)$value->y => trans_choice('interval.years', $value->y, ['value' => $value->y]),
                }),
            Text::make(__('ui.merchant_descriptor'), 'merchant_descriptor'),
        ];
    }
}
