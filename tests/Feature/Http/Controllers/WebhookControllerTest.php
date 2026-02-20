<?php

declare(strict_types=1);

use Firebase\JWT\JWT;
use Vptrading\MoneyMan\Exceptions\InvalidSignatureException;

it('stores chapa webhook events', function (): void {
    $this->withoutExceptionHandling();
    config()->set('moneyman.providers.chapa.webhook_secret', 'test_secret');
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
        'provider' => 'chapa',
        'tx_ref' => '4FGFF4FFGD3',
        'status' => 'success',
        'amount' => '400.00',
        'currency' => 'ETB',
    ]);
});

it('stores telebirr webhook events', function (): void {
    $this->withoutExceptionHandling();

    $payload = [
        'notify_url' => 'http://197.156.68.29:5050/v2/api/order-v2/mini/payment',
        'appid' => '853694808089634',
        'notify_time' => '1670575472482',
        'merch_code' => '245445',
        'merch_order_id' => '1670575560882',
        'payment_order_id' => '00801104C911443200001002',
        'total_amount' => '10.00',
        'trans_id' => '49485948475845',
        'trans_currency' => 'ETB',
        'trade_status' => 'Completed',
        'trans_end_time' => '1670575472000',
        'sign' => 'AOwWQF0QDg0jzzs5otLYOunoR65GGgC3hyr+oYn8mm1Qph6Een7C…', // Ensure you paste the full string here
        'sign_type' => 'SHA256WithRSA',
    ];

    $response = $this->postJson(route('moneyman.webhook', [
        'provider' => 'telebirr',
    ]), $payload);

    $response->assertStatus(200);

    $this->assertDatabaseHas('webhook_events', [
        'provider' => 'telebirr',
        'tx_ref' => '1670575560882',
        'status' => 'Completed',
        'amount' => 10.00,
        'currency' => 'ETB',
    ]);
});

it('stores santimpay webhook events', function (): void {
    $this->withoutExceptionHandling();
    $payload = json_decode('{
        "txnId": "d7fa8146-cb58-405a-8ca7-920cdc1f56da",
        "created_at": "2023-02-28T10:26:17.904879Z",
        "updated_at": "2023-02-28T10:26:49.042602Z",
        "thirdPartyId": "1",
        "merId": "f660f84e-7395-417b-91ff-542026c38326",
        "merName": "santimpay test company",
        "address": "Addis Ababa",
        "amount": "1",
        "currency": "ETB",
        "reason": "Payment for a coffee",
        "msisdn": "",
        "accountNumber": "",
        "paymentVia": "Telebirr",
        "refId": "5e4af4cc-99d1-4db9-a784-4ba4eb75e646",
        "successRedirectUrl": "https://santimpay.com",
        "failureRedirectUrl": "https://santimpay.com",
        "message": "payment successful",
        "status": "COMPLETED",
        "receiverWalletID": ""
        }', true);

    $privateKey = "-----BEGIN EC PRIVATE KEY-----\n".config('moneyman.providers.santimpay.private_key')."\n-----END EC PRIVATE KEY-----\n";
    $values = explode('\\n', $privateKey);

    $privateKey = implode("\n", $values);

    $sign = JWT::encode($payload, $privateKey, 'ES256');

    $response = $this->postJson(route('moneyman.webhook', [
        'provider' => 'santimpay',
    ]), $payload, [
        'HTTP_SIGNED_TOKEN' => $sign,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('webhook_events', [
        'provider' => 'santimpay',
        'tx_ref' => '5e4af4cc-99d1-4db9-a784-4ba4eb75e646',
        'status' => 'COMPLETED',
        'amount' => 1,
        'currency' => 'ETB',
    ]);
});

it('rejects chapa webhook events with invalid signature', function (): void {
    $this->withoutExceptionHandling();
    config()->set('chapa.webhook_secret', 'test_secret');

    $payload = [
        'event' => 'charge.success',
        'tx_ref' => 'TX-INVALID',
        'reference' => 'REF-INVALID',
        'amount' => '100.00',
        'charge' => '3.00',
        'currency' => 'ETB',
        'status' => 'success',
    ];

    expect(fn () => $this->postJson(route('moneyman.webhook', [
        'provider' => 'chapa',
    ]), $payload, ['x-chapa-signature' => 'bad-signature']))
        ->toThrow(InvalidSignatureException::class);

    $this->assertDatabaseCount('webhook_events', 0);
});

it('rejects santimpay webhook events with invalid signature', function (): void {
    $this->withoutExceptionHandling();

    $payload = [
        'txnId' => 'txn-invalid',
        'refId' => 'ref-invalid',
        'amount' => '1',
        'currency' => 'ETB',
        'status' => 'COMPLETED',
    ];

    expect(fn () => $this->postJson(route('moneyman.webhook', [
        'provider' => 'santimpay',
    ]), $payload, ['signed-token' => 'bad-signature']))
        ->toThrow(InvalidSignatureException::class);

    $this->assertDatabaseCount('webhook_events', 0);
});
