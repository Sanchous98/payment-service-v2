<?php

namespace App\MoonShine\Resources;

use App\Enum\Acquirers;
use App\MoonShine\Pages\Account\{DetailPage, FormPage, IndexPage};
use Illuminate\Validation\Rule;
use MoonShine\Laravel\Resources\ModelResource;
use PaymentSystem\Laravel\Models\Account;

/**
 * @extends ModelResource<Account>
 */
class AccountResource extends ModelResource
{
    protected string $model = Account::class;

    protected string $title { get => __('ui.resource.accounts'); }

    protected function afterDeleted(mixed $item): mixed
    {
        return tap($item, fn(Account $item) => $item->credentials->delete());
    }

    protected function pages(): array
    {
        return [
            IndexPage::class,
            FormPage::class,
            DetailPage::class,
        ];
    }

    protected function rules(mixed $item): array
    {
        return [
            'description' => 'nullable|string|max:255',
            'supported_currencies' => 'array',
            'external_id' => 'nullable|string|max:255|unique:accounts,external_id',
            'credentials_type' => ['required', Rule::enum(Acquirers::class)],
            'credentials.api_key' => 'required_if:credentials_type,stripe|string',
            'credentials.webhook_signing_key' => 'required_if:credentials_type,stripe|string',
            'credentials.merchant_id' => 'required_if:credentials_type,nuvei|string',
            'credentials.site_id' => 'required_if:credentials_type,nuvei|string',
            'credentials.secret_key' => 'required_if:credentials_type,nuvei|string',
        ];
    }
}
