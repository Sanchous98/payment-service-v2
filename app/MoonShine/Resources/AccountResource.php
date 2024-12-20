<?php

namespace App\MoonShine\Resources;

use Illuminate\Database\Eloquent\Relations\Relation;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\FieldsGroup;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Template;
use MoonShine\UI\Fields\Text;
use PaymentSystem\Laravel\Models\Account;
use PaymentSystem\Laravel\Stripe;
use PaymentSystem\Laravel\Nuvei;

/**
 * @extends ModelResource<Account>
 */
class AccountResource extends ModelResource
{
    protected string $model = Account::class;

    protected string $title = 'Accounts';

    private readonly string $stripe;

    private readonly string $nuvei;

    public function __construct(CoreContract $core)
    {
        parent::__construct($core);

        $this->stripe = Relation::getMorphAlias(Stripe\Models\Credentials::class);
        $this->nuvei = Relation::getMorphAlias(Nuvei\Models\Credentials::class);
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                ID::make()->sortable(),
                Flex::make([
                    Text::make('External ID', 'external_id'),
                    Text::make(__('ui.description'), 'description'),
                    Enum::make('type', 'credentials_type')
                        ->options([
                            $this->stripe => 'Stripe',
                            $this->nuvei => 'Nuvei',
                        ])
                        ->required(),
                ]),

                Template::make('credentials', 'credentials')
                    ->fields([
                        Flex::make([
                            // stripe
                            Text::make('API Key', 'api_key')
                                ->required()
                                ->showWhen('credentials_type', '=', $this->stripe)
                                ->eye(),
                            Text::make('Webhook Signing Key', 'webhook_signing_key')
                                ->required()
                                ->showWhen('credentials_type', '=', $this->stripe)
                                ->eye(),
                            // nuvei
                            Text::make('Merchant ID', 'merchant_id')
                                ->required()
                                ->showWhen('credentials_type', '=', $this->nuvei),
                            Text::make('Site ID', 'site_id')
                                ->required()
                                ->showWhen('credentials_type', '=', $this->nuvei),
                            Text::make('Secret', 'secret_key')
                                ->required()
                                ->showWhen('credentials_type', '=', $this->nuvei)
                                ->eye(),
                        ])
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

    protected function afterDeleted(mixed $item): mixed
    {
        return tap($item, fn(Account $item) => $item->credentials->delete());
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
        ];
    }
}
