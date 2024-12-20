<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Refund;

use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;

/**
 * @extends ModelResource<Refund>
 */
class RefundResource extends ModelResource
{
    protected string $model = Refund::class;

    protected string $title = 'Refunds';

    protected bool $detailInModal = true;

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->only(Action::VIEW);
    }

    protected function pages(): array
    {
        return [
            IndexPage::class,
            DetailPage::class,
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            ID::make(),
        ];
    }
}
