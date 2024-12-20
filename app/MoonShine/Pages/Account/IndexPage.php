<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\Account;

use App\Enum\Acquirers;
use App\MoonShine\Resources\AccountResource;
use MoonShine\Laravel\Pages\Crud\IndexPage as BaseIndexPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use Symfony\Component\Intl\Currencies;


/**
 * @extends BaseIndexPage<AccountResource>
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
            Enum::make('ui.type', 'credentials_type')->badge()->attach(Acquirers::class)->translatable(),
            Select::make('ui.supported_currencies', 'supported_currencies')
                ->default('ALL')
                ->options(Currencies::getCurrencyCodes())
                ->multiple()
                ->translatable(),
            Text::make('ui.external_id', 'external_id')->translatable(),
            Text::make('ui.description', 'description')->translatable(),
        ];
    }
}
