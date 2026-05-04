<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Money\Money;
use Vptrading\MoneyMan\MoneyMan;
use Vptrading\MoneyMan\ValueObjects\User;

beforeEach(function (): void {
    Cache::forget('boa_ussd.access_token');
    Cache::forget('boa_ussd.refresh_token');
    Cache::forget('boa_ussd.token_refresh_lock');

    config()->set('moneyman.providers.boa_ussd.base_url', 'https://api.boa.test');
    config()->set('moneyman.providers.boa_ussd.client_id', 'test-client-id');
    config()->set('moneyman.providers.boa_ussd.client_secret', 'test-client-secret');
    config()->set('moneyman.providers.boa_ussd.refresh_token', 'test-refresh-token');
    config()->set('moneyman.providers.boa_ussd.merchant_name', 'Test Merchant');
    config()->set('moneyman.providers.boa_ussd.merchant_account', '1234567890');
    config()->set('moneyman.providers.boa_ussd.api_key', 'test-api-key');
});

it('initiates a transaction', function (): void {
    Http::fake([
        config('moneyman.providers.boa_ussd.base_url').'/ussd/push/oauth2/token' => Http::response([
            'access_token' => 'access-token-1',
            'refresh_token' => 'refresh-token-1',
        ], 200),
        config('moneyman.providers.boa_ussd.base_url').'/ussd/push' => Http::response([
            'pushUSSDResult' => [
                'paymentStatus' => 'PENDING_USER_PIN',
                'billNumber' => 'mm_ref_abc123',
            ],
        ], 200),
    ]);

    $response = MoneyMan::provider('boa_ussd')->initiate(
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
    expect($response->message)->toBe('PENDING_USER_PIN');
    expect($response->transactionId)->toBe('mm_ref_abc123');

    Http::assertSentCount(2);
    Http::assertSent(fn ($request) => $request->url() === config('moneyman.providers.boa_ussd.base_url').'/ussd/push' && $request->hasHeader('Authorization', 'Bearer access-token-1'));
});

it('returns error when initiate request fails', function (): void {
    Http::fake([
        config('moneyman.providers.boa_ussd.base_url').'/ussd/push/oauth2/token' => Http::response([
            'access_token' => 'access-token-1',
            'refresh_token' => 'refresh-token-1',
        ], 200),
        config('moneyman.providers.boa_ussd.base_url').'/ussd/push' => Http::response([
            'pushUSSDResult' => [
                'ResultDesc' => 'Invalid account',
            ],
        ], 400),
    ]);

    $response = MoneyMan::provider('boa_ussd')->initiate(
        Money::ETB(100),
        new User(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            phoneNumber: '1234567890'
        ),
        'https://example.com/return'
    );

    expect($response->status)->toBe('error');
    expect($response->message)->toBe('Invalid account');
});

it('throws invalid argument exception if required config is missing', function (): void {
    $originalClientId = config('moneyman.providers.boa_ussd.client_id');
    config()->set('moneyman.providers.boa_ussd.client_id', null);

    expect(fn () => MoneyMan::provider('boa_ussd')->initiate(
        Money::ETB(100),
        new User(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            phoneNumber: '1234567890'
        ),
        'https://example.com/return'
    ))->toThrow(InvalidArgumentException::class);

    config()->set('moneyman.providers.boa_ussd.client_id', $originalClientId);
});

it('throws for verify operation', function (): void {
    expect(fn () => MoneyMan::provider('boa_ussd')->verify('trx-id'))
        ->toThrow(LogicException::class);
});

it('throws for refund operation', function (): void {
    expect(fn () => MoneyMan::provider('boa_ussd')->refund('trx-id', Money::ETB(1000), 'Customer request'))
        ->toThrow(LogicException::class);
});

it('uses cached refresh token when available and rotates tokens', function (): void {
    Cache::put('boa_ussd.refresh_token', 'cached-refresh-token', now()->addDay());

    Http::fake([
        config('moneyman.providers.boa_ussd.base_url').'/ussd/push/oauth2/token' => Http::response([
            'access_token' => 'access-token-2',
            'refresh_token' => 'refresh-token-2',
        ], 200),
        config('moneyman.providers.boa_ussd.base_url').'/ussd/push' => Http::response([
            'pushUSSDResult' => [
                'paymentStatus' => 'PENDING_USER_PIN',
                'billNumber' => 'mm_ref_abc999',
            ],
        ], 200),
    ]);

    MoneyMan::provider('boa_ussd')->initiate(
        Money::ETB(100),
        new User(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            phoneNumber: '1234567890'
        ),
        'https://example.com/return'
    );

    Http::assertSent(fn ($request) => $request->url() === config('moneyman.providers.boa_ussd.base_url').'/ussd/push/oauth2/token'
        && $request['refresh_token'] === 'cached-refresh-token'
        && $request['client_secret'] === config('moneyman.providers.boa_ussd.client_secret'));

    expect(Cache::get('boa_ussd.access_token'))->toBe('access-token-2');
    expect(Cache::get('boa_ussd.refresh_token'))->toBe('refresh-token-2');
});

it('falls back to configured refresh token when cache is empty', function (): void {
    Cache::forget('boa_ussd.refresh_token');

    Http::fake([
        config('moneyman.providers.boa_ussd.base_url').'/ussd/push/oauth2/token' => Http::response([
            'access_token' => 'access-token-3',
            'refresh_token' => 'refresh-token-3',
        ], 200),
        config('moneyman.providers.boa_ussd.base_url').'/ussd/push' => Http::response([
            'pushUSSDResult' => [
                'paymentStatus' => 'PENDING_USER_PIN',
                'billNumber' => 'mm_ref_fallback',
            ],
        ], 200),
    ]);

    MoneyMan::provider('boa_ussd')->initiate(
        Money::ETB(100),
        new User(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            phoneNumber: '1234567890'
        ),
        'https://example.com/return'
    );

    Http::assertSent(fn ($request) => $request->url() === config('moneyman.providers.boa_ussd.base_url').'/ussd/push/oauth2/token'
        && $request['refresh_token'] === config('moneyman.providers.boa_ussd.refresh_token'));
});
