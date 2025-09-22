<?php

use Alazark94\MoneyMan\MoneyMan;
use Alazark94\MoneyMan\ValueObjects\User;
use Illuminate\Support\Facades\Http;
use Money\Money;

it('initiates a transaction', function () {

    $response = MoneyMan::provider('telebirr')->initiate(
        Money::ETB(100),
        new User(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            phoneNumber: '1234567890'
        ),
        'https://example.com/return',
        reason: 'Payment'
    );


    expect($response->status)->toBe('success');
    expect($response->checkoutUrl)->toBeString();
    expect($response->transactionId)->toBeString();
});


it('throws invalid argument exception if secret key is not set', function (): void {
    config()->set('moneyman.providers.telebirr.merchant_app_id', null);

    expect(fn() => MoneyMan::provider('telebirr')->initiate(
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

    $initiate = MoneyMan::provider('telebirr')
        ->initiate(
            Money::ETB(1000),
            new User(
                firstName: 'John',
                lastName: 'Doe',
                email: 'johndoe@example.com',
                phoneNumber: '0912345678'
            ),
            returnUrl: 'https://return-url.com',
            reason: 'Some Reason'
        );
    $response = MoneyMan::provider('telebirr')->verify($initiate->transactionId);

    expect($response->status)->toBe('PAY_FAILED');
});

it('can refund transactions', function (): void {
    $initiate = MoneyMan::provider('telebirr')
        ->initiate(
            Money::ETB(1000),
            new User(
                firstName: 'John',
                lastName: 'Doe',
                email: 'johndoe@example.com',
                phoneNumber: '0912345678'
            ),
            returnUrl: 'https://return-url.com',
            reason: 'Some Reason'
        );
    $response = MoneyMan::provider('telebirr')->refund($initiate->transactionId);

    expect($response->status)->toBe('FAIL');
});
