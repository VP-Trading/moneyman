<?php

declare(strict_types=1);

use Alazark94\MoneyMan\MoneyMan;
use Alazark94\MoneyMan\ValueObjects\User;
use Illuminate\Support\Facades\Http;
use Money\Money;

it('initiates a transaction', function (): void {
    Http::fake(function () {
        return Http::response(
            json_decode(
                '{"message": "Hosted Link", "status": "success", "data": {"checkout_url": "https://checkout.chapa.co/checkout/payment/V38JyhpTygC9QimkJrdful9oEjih0heIv53eJ1MsJS6xG"}}',
                true
            ),
            200
        );
    });

    /** @var \Vptrading\ChapaLaravel\Dtos\AcceptPaymentResponse $response */
    $response = MoneyMan::provider('chapa')->initiate(
        Money::ETB(100),
        new User(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            phoneNumber: '1234567890'
        ),
        'https://example.com/return'
    );

    expect($response->status)->toBe('success');
    expect($response->checkoutUrl)->toBeString();
    expect($response->transactionId)->toBeString();
});

it('initiates payments with customization', function (): void {
    Http::fake(function () {
        return Http::response(
            json_decode(
                '{"message": "Hosted Link", "status": "success", "data": {"checkout_url": "https://checkout.chapa.co/checkout/payment/V38JyhpTygC9QimkJrdful9oEjih0heIv53eJ1MsJS6xG"}}',
                true
            ),
            200
        );
    });

    $response = MoneyMan::provider('chapa')->initiate(
        Money::ETB(100),
        new User(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            phoneNumber: '1234567890'
        ),
        'https://example.com/return',
        parameters: [
            'logo' => 'https://your-logo-url.com',
        ]
    );

    expect($response->status)->toBe('success');
    expect($response->checkoutUrl)->toBeString();
    expect($response->transactionId)->toBeString();
});

it('throws invalid argument exception if secret key is not set', function (): void {
    config()->set('moneyman.providers.chapa.secret_key', null);

    expect(fn () => MoneyMan::provider('chapa')->initiate(
        Money::ETB(100),
        new User(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            phoneNumber: '1234567890'
        ),
        'https://example.com/return'
    ))->toThrow(\InvalidArgumentException::class);
});

it('verifies payments', function (): void {
    $tx_ref = 'vp_chapa_'.str()->random(10);
    Http::fake(function () use ($tx_ref) {
        return Http::response(
            json_decode(
                '{
                    "message": "Payment details",
                    "status": "success",
                    "data": {
                        "first_name": "John",
                        "last_name": "Doe",
                        "email": "john.doe@example.com",
                        "currency": "ETB",
                        "amount": 100,
                        "charge": 3.5,
                        "mode": "test",
                        "method": "test",
                        "type": "API",
                        "status": "success",
                        "reference": "6jnheVKQEmy",
                        "tx_ref": "'.$tx_ref.'",
                        "customization": {
                            "title": "Payment for my favourite merchant",
                            "description": "I love online payments",
                            "logo": null
                        },
                        "meta": null,
                        "created_at": "2023-02-02T07:05:23.000000Z",
                        "updated_at": "2023-02-02T07:05:23.000000Z"
                        }
                }',
                true
            ),
            200
        );
    });
    $response = MoneyMan::provider('chapa')->verify($tx_ref);

    expect($response->status)->toBe('success');
    expect($response->data['tx_ref'])->toBe($tx_ref);
});

it('can refund transactions', function (): void {
    $tx_ref = 'vp_chapa_'.str()->random(10);
    Http::fake(function () {
        return Http::response(
            json_decode(
                '{
                    "message": "Refund initiated successfully. Processing time: 1-3 business days",
                    "status": "success",
                    "data": {
                        "id": 730,
                        "chapa_reference": "APezQ1KKswbb",
                        "bank_reference": "BLC9JI3G21",
                        "amount": "100.00",
                        "ref": "MERC-DIS-REF-s223VGvQFJk",
                        "currency": "ETB",
                        "status": "Refund Initiated",
                        "reason": null,
                        "merchant_reference": "OTAS379IOSHJ",
                        "created_at": "2024-12-13T17:33:27.000000Z",
                        "updated_at": "2024-12-13T17:33:27.000000Z"
                    }
                }',
                true
            ),
            200
        );
    });

    $response = MoneyMan::provider('chapa')->refund($tx_ref, Money::ETB(10000), 'Customer requested refund');

    expect($response->status)->toBe('success');
    expect($response->data['chapa_reference'])->toBeString();
    expect($response->data['amount'])->toBe('100.00');
});
