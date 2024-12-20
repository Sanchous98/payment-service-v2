<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\SubscriptionPlan;

use App\Models\SubscriptionPlan;
use App\MoonShine\Resources\SubscriptionPlanResource;
use DateInterval;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\MoneyFormatter;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\Pages\Crud\FormPage as BaseFormPage;
use MoonShine\UI\Components\FieldsGroup;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Fields\Fieldset;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Template;
use MoonShine\UI\Fields\Text;
use PaymentSystem\ValueObjects\MerchantDescriptor;
use Symfony\Component\Intl\Currencies;


/**
 * @extends BaseFormPage<SubscriptionPlanResource>
 */
class FormPage extends BaseFormPage
{
    private readonly MoneyFormatter $formatter;

    public function __construct(CoreContract $core)
    {
        parent::__construct($core);
        $this->formatter = new DecimalMoneyFormatter(new ISOCurrencies());
    }

    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Text::make('ui.name', 'name')->translatable()->required(),
                Text::make('ui.description', 'description')->translatable()->default('')->required(),
                Flex::make([
                    Fieldset::make('ui.price', [
                        Number::make(column: 'amount')
                            ->min(0.5)
                            ->max(999999)
                            ->step(0.01)
                            ->buttons()
                            ->required()
                            ->changeFill(fn(SubscriptionPlan $plan) => isset($plan->money) ? $this->formatter->format($plan->money) : null),
                        Select::make(column: 'currency')
                            ->options(collect(Currencies::getCurrencyCodes())->flatMap(fn(string $code) => [$code => $code])->toArray())
                            ->default('USD')
                            ->required(),
                    ])->translatable(),
                    Template::make(__('ui.interval'), 'interval')
                        ->fields([
                            Number::make(column: 'count')->min(1)->required()->buttons()->step(1),
                            Select::make(column: 'unit')->required()->options([
                                'd' => __('ui.day'),
                                'w' => __('ui.week'),
                                'm' => __('ui.month'),
                                'y' => __('ui.year'),
                            ])
                        ])
                        ->changeFill(fn(mixed $data) => match(true) {
                            data_get($data, 'interval') === null => [],
                            (bool)data_get($data, 'interval')->d => data_get($data, 'interval')->d % 7 === 0 ? ['count' => data_get($data, 'interval')->d / 7, 'unit' => 'w'] : ['count' => data_get($data, 'interval')->d, 'unit' => 'd'],
                            (bool)data_get($data, 'interval')->m => ['count' => data_get($data, 'interval')->m, 'unit' => 'm'],
                            (bool)data_get($data, 'interval')->y => ['count' => data_get($data, 'interval')->y, 'unit' => 'y'],
                        })
                        ->changeRender(fn(array $value, Template $ctx) => Fieldset::make('ui.interval', $ctx->getPreparedFields())->fill($value))
                        ->onApply(function(SubscriptionPlan $item, array $value) {
                            return tap($item, fn(SubscriptionPlan $item) => $item->interval = match ($value['unit']) {
                                'd' => new DateInterval('P' . $value['count'] . 'D'),
                                'w' => new DateInterval('P' . $value['count']*7 . 'D'),
                                'm' => new DateInterval('P' . $value['count'] . 'M'),
                                'y' => new DateInterval('P' . $value['count'] . 'Y'),
                            });
                        }),
                ]),
                Text::make('ui.merchant_descriptor', 'merchant_descriptor')
                    ->default('')
                    ->required()
                    ->translatable()
                    ->onApply(function(SubscriptionPlan $item, string $value) {
                        return tap($item, fn(SubscriptionPlan $item) => $item->merchant_descriptor = new MerchantDescriptor($value));
                    }),
            ])
        ];
    }
}
