<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Enum\IntervalUnit;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Core\Exceptions\ResourceException;
use MoonShine\Laravel\Traits\Resource\ResourceModelQuery;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Laravel\Resources\ModelResource;
use App\MoonShine\Pages\SubscriptionPlan\{DetailPage, FormPage, IndexPage};
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
            IndexPage::class,
            FormPage::class,
            DetailPage::class,
        ];
    }

    protected function rules(mixed $item): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:.5|max:999999.99',
            'currency' => 'required|currency',
            'interval_count' => 'required|integer|min:1',
            'interval_unit' => ['required', Rule::enum(IntervalUnit::class)],
            'merchant_descriptor' => 'string|max:22'
        ];
    }

    public function massDelete(array $ids): void
    {
        SubscriptionPlan::query()
            ->whereIn('id', $ids)
            ->cursor()
            ->each(fn(SubscriptionPlan $plan) => $this->delete($plan));
    }

    public function delete(mixed $item, ?FieldsContract $fields = null): bool
    {
        try {
            $this->dispatcher->dispatchSync(new DeleteSubscriptionPlanJob($item->id));
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

            $id = Uuid::fromString((string)data_get($item, 'id', $item->newUniqueId()));

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

            $this->dispatcher->dispatchSync($job);
            $item = tap($this->getModel()::query()->findOrFail($id), function (Model $model) {
                $model->wasRecentlyCreated = $this->isRecentlyCreated;
            });
            $item = $this->afterSave($item, $fields);
        } catch (QueryException $queryException) {
            throw new ResourceException($queryException->getMessage(), previous: $queryException);
        }

        $this->setItem($item);

        return $item;
    }
}
