<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\Account;

use App\Enum\Acquirers;
use App\MoonShine\Resources\AccountResource;
use MoonShine\Laravel\Pages\Crud\DetailPage as BaseDetailPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;


/**
 * @extends BaseDetailPage<AccountResource>
 */
class DetailPage extends BaseDetailPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Enum::make('ui.type', 'credentials_type')->attach(Acquirers::class)->translatable(),
            Text::make('ui.external_id', 'external_id')->translatable(),
            Text::make('ui.description', 'description')->translatable(),
        ];
    }
}
