<?php

use Database\Factories\AccountFactory;

uses(\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests::class);

it('returns a successful response', function () {
    $account = AccountFactory::new()->stripe()->create();

    $tokenId = $this->post('/api/v2/tokens', [
        'type' => 'card',
        'card' => [
            'number' => '4242424242424242',
            'expiration_month' => 12,
            'expiration_year' => 34,
            'cvc' => '999',
            'holder' => 'Andrea Palladio',
        ]
    ])->json('data.id');
});
