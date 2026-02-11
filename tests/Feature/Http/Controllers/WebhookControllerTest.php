<?php

declare(strict_types=1);

it('stores chapa webhook events', function (): void {
    $this->withoutExceptionHandling();
    config()->set('chapa.webhook_secret', 'test_secret');
    $payload = [
        'event' => 'charge.success',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'johndoe@example.com',
        'mobile' => '25190000000',
        'currency' => 'ETB',
        'amount' => '400.00',
        'charge' => '12.00',
        'status' => 'success',
        'mode' => 'live',
        'reference' => 'AP634JFwEbxd',
        'created_at' => '2023-08-27T19:21:18.000000Z',
        'updated_at' => '2023-08-27T19:21:27.000000Z',
        'type' => 'API',
        'tx_ref' => '4FGFF4FFGD3',
        'payment_method' => 'telebirr',
        'customization' => [
            'title' => null,
            'description' => null,
            'logo' => null,
        ],
        'meta' => null,
    ];

    $hash = hash_hmac('sha256', json_encode($payload), 'test_secret');
    $response = $this->postJson(route('moneyman.webhook', [
        'provider' => 'chapa',
    ]), $payload, ['x-chapa-signature' => $hash]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('webhook_events', [
        'event_type' => 'charge.success',
        'tx_ref' => '4FGFF4FFGD3',
        'status' => 'success',
        'amount' => '400.00',
        'currency' => 'ETB',
    ]);
});
