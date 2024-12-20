<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Carbon\WrapperClock;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Str;
use PaymentSystem\Enum\SubscriptionStatusEnum;
use PaymentSystem\Laravel\Jobs\AuthorizePaymentIntentJob;
use PaymentSystem\Laravel\Jobs\CapturePaymentIntentJob;
use PaymentSystem\Laravel\Uuid;
use PaymentSystem\Repositories\PaymentIntentRepositoryInterface;
use PaymentSystem\Repositories\SubscriptionRepositoryInterface;
use PaymentSystem\Repositories\TenderRepositoryInterface;

class ExecuteScheduledPayments extends Command
{
    protected $signature = 'payments:subscription:execute-scheduled-payments';

    protected $description = 'Executes scheduled payments';

    public function __invoke(TenderRepositoryInterface $tenders, SubscriptionRepositoryInterface $subscriptions, PaymentIntentRepositoryInterface $paymentIntents, Dispatcher $dispatcher): void
    {
        Subscription::unguard();
        Subscription::with(['account', 'subscriptionPlan'])
            ->cursor()
            ->each(fn(Subscription $subscription) => $subscription->update(['status' => $subscriptions->retrieve(Uuid::fromString($subscription->id))->getStatus()]))
            ->filter(fn(Subscription $subscription) => !$subscriptions->retrieve($subscription->id)->is(SubscriptionStatusEnum::ACTIVE))
            ->filter(fn(Subscription $subscription) => !$subscriptions->retrieve($subscription->id)->is(SubscriptionStatusEnum::CANCELLED))
            ->each(function(Subscription $subscription) use($dispatcher, $tenders, $subscriptions, $paymentIntents) {
                $dispatcher->dispatchSync(new AuthorizePaymentIntentJob(
                    $id = new Uuid(Str::uuid7()),
                    $subscription->subscriptionPlan->money,
                    $subscription->account,
                    $tenders->retrieve($subscription->payment_method_id),
                    $subscription->subscriptionPlan->merchant_descriptor,
                    "Subscription payment for $subscription->subscription_plan_id",
                    subscription: $subscriptions->retrieve($subscription->id)
                ));

                $dispatcher->dispatchSync(new CapturePaymentIntentJob($id, $subscription->account));

                $subscriptions->retrieve($subscription->id)->pay($paymentIntents->retrieve($id), new WrapperClock(now()));
            });
    }
}
