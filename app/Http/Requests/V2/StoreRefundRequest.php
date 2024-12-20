<?php

declare(strict_types=1);

namespace App\Http\Requests\V2;

use App\Models\PaymentIntent;
use Illuminate\Foundation\Http\FormRequest;

class StoreRefundRequest extends FormRequest
{
    private PaymentIntent $intent;


    public function paymentIntent(): PaymentIntent
    {
        return $this->intent ??= PaymentIntent::with(['refunds', 'account'])->findOrFail($this->str('payment_intent_id'));
    }

    public function rules(): array
    {
        return [
            'payment_intent_id' => 'required|uuid|exists:payment_intents,id',
            'amount' => 'required|numeric|min:0|max:'. $this->maxAmountToRefund(),
        ];
    }

    private function maxAmountToRefund(): string
    {
        return $this->paymentIntent()->availableForRefund->getAmount();
    }
}
