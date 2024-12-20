<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\SubscriptionPlan;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Money;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Core\Exceptions\ResourceException;
use MoonShine\Laravel\Traits\Resource\ResourceModelQuery;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Laravel\Resources\ModelResource;
use App\MoonShine\Pages\SubscriptionPlan as SubscriptionPlanPages;
use PaymentSystem\Laravel\Jobs\CreateSubscriptionPlanJob;
use PaymentSystem\Laravel\Jobs\DeleteSubscriptionPlanJob;
use PaymentSystem\Laravel\Jobs\UpdateSubscriptionPlanJob;
use PaymentSystem\Laravel\Uuid;
use PaymentSystem\Repositories\SubscriptionPlanRepositoryInterface;

/**
 * @extends ModelResource<SubscriptionPlan>
 */
#[Icon('arrow-path')]
class SubscriptionPlanResource extends ModelResource
{
    use ResourceModelQuery;

    protected string $title { get => __('ui.resource.subscription_plans'); }

    protected string $model = SubscriptionPlan::class;

    private SubscriptionPlanRepositoryInterface $repository;

    private Dispatcher $dispatcher;

    public function __construct(CoreContract $core)
    {
        parent::__construct($core);

        $this->repository = $core->getContainer(SubscriptionPlanRepositoryInterface::class);
        $this->dispatcher = $core->getContainer(Dispatcher::class);
    }

    protected function pages(): array
    {
        return [
            SubscriptionPlanPages\IndexPage::class,
            SubscriptionPlanPages\FormPage::class,
            SubscriptionPlanPages\DetailPage::class,
        ];
    }

    public function prepareForValidation(): void
    {
        if (!request()->has('currency')) {
            return;
        }

        $currency = new Currency((string)request()->str('currency'));
        $amount = Money::getCalculator()
            ::multiply((string)request()->str('amount'), (string)(10 ** new ISOCurrencies()->subunitFor($currency)));

        request()->merge([
            'amount' => Money::getCalculator()::ceil($amount),
        ]);
    }

    protected function rules(mixed $item): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:50|max:99999999',
            'currency' => 'required|currency',
            'interval.count' => 'required|integer|min:1',
            'interval.unit' => 'required|in:d,w,m,y',
            'merchant_descriptor' => 'string|max:22'
        ];
    }

    public function massDelete(array $ids): void
    {
        SubscriptionPlan::query()->find($ids)->each(function (SubscriptionPlan $plan) {
            $this->dispatcher->dispatchSync(new DeleteSubscriptionPlanJob(Uuid::fromString($plan->id)));
        });
    }

    public function delete(mixed $item, ?FieldsContract $fields = null): bool
    {
        try {
            $this->dispatcher->dispatchSync(new DeleteSubscriptionPlanJob(Uuid::fromString($item->id)));
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function save(mixed $item, ?FieldsContract $fields = null): mixed
    {
        $fields ??= $this->getFormFields()->onlyFields(withApplyWrappers: true);

        $fields->fill($item->toArray(), $this->getCaster()->cast($item));

        try {
            $fields->each(static fn (FieldContract $field): mixed => $field->beforeApply($item));

            $id = Uuid::fromString(data_get($item, 'id', $item->newUniqueId()));

            if (! $item->exists) {
                $item = $this->beforeCreating($item);
                $this->isRecentlyCreated = true;

                $fields->withoutOutside()
                    ->each(fn (FieldContract $field): mixed => $field->apply($this->fieldApply($field), $item));
                $job = new CreateSubscriptionPlanJob(
                    $id,
                    data_get($item, 'name'),
                    data_get($item, 'description'),
                    data_get($item, 'money'),
                    data_get($item, 'interval'),
                    data_get($item, 'merchant_descriptor'),
                );
            } else {
                $item = $this->beforeUpdating($item);
                $this->isRecentlyCreated = false;

                $fields->withoutOutside()
                    ->each(fn (FieldContract $field): mixed => $field->apply($this->fieldApply($field), $item));
                $job = new UpdateSubscriptionPlanJob(
                    $id,
                    data_get($item, 'name'),
                    data_get($item, 'description'),
                    data_get($item, 'money'),
                    data_get($item, 'interval'),
                    data_get($item, 'merchant_descriptor'),
                );
            }

            $this->dispatcher->dispatchNow($job);
            $item = $this->afterSave(tap($this->getModel()::query()->findOrFail($id), function (Model $model) {
                $model->wasRecentlyCreated = true;
            }), $fields);
        } catch (QueryException $queryException) {
            throw new ResourceException($queryException->getMessage(), previous: $queryException);
        }

        $this->setItem($item);

        return $item;
    }
}
