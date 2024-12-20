<?php

namespace App\Http\Requests\V2;

use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Http\FormRequest;
use PaymentSystem\Laravel\Models\Account;

class StoreSubscriptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'account_id' => 'required|uuid|exists:accounts,id',
            'plan_id' => 'required|uuid|exists:subscription_plans,id',
            'payment_method_id' => 'required|uuid|exists:payment_methods,id',
        ];
    }

    public function account(): Account
    {
        return Account::query()->findOrFail($this->str('account_id'));
    }

    public function subscriptionPlan(): SubscriptionPlan
    {
        return SubscriptionPlan::query()->findOrFail($this->str('plan_id'));
    }
}
