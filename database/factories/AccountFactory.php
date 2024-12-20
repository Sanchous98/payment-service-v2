<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use PaymentSystem\Laravel\Models\Account;
use PaymentSystem\Laravel\Stripe\Models\Credentials as StripeCredentials;
use PaymentSystem\Laravel\Nuvei\Models\Credentials as NuveiCredentials;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'id' => Str::uuid7(),
        ];
    }

    public function stripe($apiKey = null, $webhookSigningKey = null): self
    {
        $apiKey ??= config('services.stripe.api_key');
        $webhookSigningKey ??= config('services.stripe.webhook_signing_key');

        return $this->state(fn(array $attributes) => [
            'credentials_id' => StripeCredentials::query()->create([
                'api_key' => $apiKey,
                'webhook_signing_key' => $webhookSigningKey,
            ])->id,
            'description' => 'Stripe Test',
            'credentials_type' => Relation::getMorphAlias(StripeCredentials::class),
        ]);
    }

    public function nuvei($merchantId = null, $siteId = null, $secretKey= null): self
    {
        $merchantId ??= config('services.nuvei.merchant_id');
        $siteId ??= config('services.nuvei.site_id');
        $secretKey ??= config('services.nuvei.secret_key');

        return $this->state(fn (array $attributes) => [
            'credentials_id' => NuveiCredentials::query()->create([
                'merchant_id' => $merchantId,
                'site_id' => $siteId,
                'secret_key' => $secretKey,
            ])->id,
            'description' => 'Nuvei Test',
            'credentials_type' => Relation::getMorphAlias(NuveiCredentials::class),
        ]);
    }
}
