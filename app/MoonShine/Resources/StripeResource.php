<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use PaymentSystem\Laravel\Models\Account;
use PaymentSystem\Laravel\Stripe\Models\Credentials;

class StripeResource extends ModelResource
{
    protected string $model = Account::class;

    protected array $with = ['credentials'];

    protected string $title = 'Stripe';

    public function getQuery(): Builder
    {
        return parent::getQuery()
            ->where(['credentials_type' => Relation::getMorphAlias(Credentials::class)]);
    }

    protected function search(): array
    {
        return ['id', 'external_id', 'description'];
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('External ID', 'external_id'),
            Text::make(__('ui.description'), 'description'),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('External ID', 'external_id'),
            Text::make(__('ui.description'), 'description'),
            Text::make('API Key', 'credentials.api_key')->eye(),
            Text::make('Webhook Signing Key', 'credentials.webhook_signing_key')->eye(),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            Box::make([
                Flex::make([
                    Text::make('External ID', 'external_id'),
                    Text::make('Description', 'description'),
                ]),
                Flex::make([
                    Text::make('API Key', 'credentials.api_key')->eye(),
                    Text::make('Webhook Signing Key', 'credentials.webhook_signing_key')->eye(),
                ]),
            ]),
        ];
    }

    /**
     * @param Account $item
     */
    public function save(mixed $item, ?FieldsContract $fields = null): Account
    {
        $fields ??= $this->getFormFields()->onlyFields();

        $credentials = $item->credentials ?? new Credentials();
        $credentials->api_key = $fields->findByColumn('credentials.api_key')->getRequestValue();
        $credentials->webhook_signing_key = $fields->findByColumn('credentials.webhook_signing_key')->getRequestValue();
        $credentials->save();
        $item->credentials()->associate($credentials);

        return parent::save($item, $fields);
    }
}
