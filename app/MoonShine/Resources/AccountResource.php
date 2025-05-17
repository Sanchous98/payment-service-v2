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

    protected string $title { get => 'ui.resource.accounts'; }

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
                    Text::make('ui.external_id', 'external_id')->translateable(),
                    Text::make('ui.description', 'description')->translateable(),
                    Enum::make('ui.type', 'credentials_type')
                        ->translateable()
                        ->options([
                            $this->stripe => 'Stripe',
                            $this->nuvei => 'Nuvei',
                        ])
                        ->required(),
                ]),

                Template::make('ui.credentials', 'credentials')
                    ->translateable()
                    ->fields([
                        Flex::make([
                            // stripe
                            Text::make('ui.api_key', 'api_key')
                                ->translateable()
                                ->required()
                                ->showWhen('credentials_type', '=', $this->stripe)
                                ->eye(),
                            Text::make('ui.webhook_signing_key', 'webhook_signing_key')
                                ->translatable()
                                ->required()
                                ->showWhen('credentials_type', '=', $this->stripe)
                                ->eye(),
                            // nuvei
                            Text::make('ui.merchant_id', 'merchant_id')
                                ->translateable()
                                ->required()
                                ->showWhen('credentials_type', '=', $this->nuvei),
                            Text::make('ui.site_id', 'site_id')
                                ->translateable()
                                ->required()
                                ->showWhen('credentials_type', '=', $this->nuvei),
                            Text::make('ui.secret', 'secret_key')
                                ->translateable()
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
            Text::make('ui.external_id', 'external_id')->translateable(),
            Text::make('ui.description', 'description')->translateable(),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('ui.external_id', 'external_id')->translateable(),
            Text::make('ui.description', 'description')->translateable(),
        ];
    }
}
