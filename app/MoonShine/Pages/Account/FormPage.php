<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\Account;

use App\Enum\Acquirers;
use App\MoonShine\Resources\AccountResource;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Laravel\Pages\Crud\FormPage as BaseFormPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Components\FieldsGroup;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Template;
use MoonShine\UI\Fields\Text;
use PaymentSystem\Laravel\Models\Account;
use Symfony\Component\Intl\Currencies;


/**
 * @extends BaseFormPage<AccountResource>
 */
class FormPage extends BaseFormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make()->sortable(),
                Flex::make([
                    Text::make('ui.external_id', 'external_id')->translatable(),
                    Text::make('ui.description', 'description')
                        ->default('')
                        ->translatable()
                        ->onRequestValue(fn (mixed $value, string $column, string $default) => $value ?: $default),
                    Select::make('ui.supported_currencies', 'supported_currencies')
                        ->options(Currencies::getCurrencyCodes())
                        ->multiple()
                        ->searchable()
                        ->translatable()
                        ->onApply(fn(Account $item, array $values) => $item->supported_currencies = $values),
                    Enum::make('ui.type', 'credentials_type')
                        ->translatable()
                        ->attach(Acquirers::class)
                        ->required(),
                ])->itemsAlign('top'),

                Template::make('ui.credentials', 'credentials')
                    ->translatable()
                    ->fields([
                        Flex::make([
                            // stripe
                            Text::make('ui.api_key', 'api_key')
                                ->translatable()
//                                ->required()
                                ->showWhen('credentials_type', '=', Acquirers::STRIPE->value)
                                ->eye(),
                            Text::make('ui.webhook_signing_key', 'webhook_signing_key')
                                ->translatable()
//                                ->required()
                                ->showWhen('credentials_type', '=', Acquirers::STRIPE->value)
                                ->eye(),
                            // nuvei
                            Text::make('ui.merchant_id', 'merchant_id')
                                ->translatable()
//                                ->required()
                                ->showWhen('credentials_type', '=', Acquirers::NUVEI->value),
                            Text::make('ui.site_id', 'site_id')
                                ->translatable()
//                                ->required()
                                ->showWhen('credentials_type', '=', Acquirers::NUVEI->value),
                            Text::make('ui.secret', 'secret_key')
                                ->translatable()
//                                ->required()
                                ->showWhen('credentials_type', '=', Acquirers::NUVEI->value)
                                ->eye(),
                        ])->itemsAlign('top')
                    ])
                    ->changeFill(fn(Account $value) => $value->credentials)
                    ->changeRender(fn(mixed $value, Template $ctx) => FieldsGroup::make($ctx->getPreparedFields())->fill($value?->toArray() ?? []))
                    ->onApply(function (Account $value, array $values) {
                        $credentials = $value->credentials;

                        if ($value->getOriginal('credentials_type') !== $value->credentials_type) {
                            $value->credentials()->delete();
                            $credentials = $value->credentials()->createModelByType($value->credentials_type);
                        }

                        $credentials->forceFill($values);
                        $value->credentials()->associate(tap($credentials)->save());

                        return $value;
                    }),
            ])
        ];
    }
}
