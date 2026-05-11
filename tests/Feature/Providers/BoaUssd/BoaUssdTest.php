<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Money\Money;
use Vptrading\MoneyMan\Models\BoAToken;
use Vptrading\MoneyMan\MoneyMan;
use Vptrading\MoneyMan\ValueObjects\User;

beforeEach(function (): void {
    config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

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

it('verifies a transaction status', function (): void {
    Http::fake([
        config('moneyman.providers.boa_ussd.base_url').'/ussd/push/oauth2/token' => Http::response([
            'access_token' => 'access-token-verify',
            'refresh_token' => 'refresh-token-verify',
        ], 200),
        config('moneyman.providers.boa_ussd.base_url').'/ussd/push/getStatus*' => Http::response([
            'status' => 'SUCCESS',
            'message' => 'Paid',
            'reference' => 'mm_ref_verify_1',
        ], 200),
    ]);

    $response = MoneyMan::provider('boa_ussd')->verify('mm_ref_verify_1');

    expect($response->isSuccessful())->toBeTrue();
    expect($response->transactionId)->toBe('mm_ref_verify_1');
    expect($response->message)->toBe('Paid');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/ussd/push/getStatus')
        && str_contains($request->url(), 'reference=mm_ref_verify_1'));
});

it('throws for refund operation', function (): void {
    expect(fn () => MoneyMan::provider('boa_ussd')->refund('trx-id', Money::ETB(1000), 'Customer request'))
        ->toThrow(LogicException::class);
});

it('uses stored refresh token when available and rotates tokens', function (): void {
    BoAToken::query()->create([
        'refresh_token' => 'stored-refresh-token',
        'access_token' => null,
    ]);

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
        && $request['refresh_token'] === 'stored-refresh-token'
        && $request['client_secret'] === config('moneyman.providers.boa_ussd.client_secret'));

    expect(Cache::get('boa_ussd.access_token'))->toBe('access-token-2');
    expect(Cache::get('boa_ussd.refresh_token'))->toBe('refresh-token-2');
    expect((string) BoAToken::query()->value('access_token'))->toBe('access-token-2');
    expect((string) BoAToken::query()->value('refresh_token'))->toBe('refresh-token-2');
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

it('uses stored access token when cache is empty', function (): void {
    BoAToken::query()->create([
        'access_token' => 'stored-access-token',
        'refresh_token' => 'stored-refresh-token',
    ]);

    Http::fake([
        config('moneyman.providers.boa_ussd.base_url').'/ussd/push' => Http::response([
            'pushUSSDResult' => [
                'paymentStatus' => 'PENDING_USER_PIN',
                'billNumber' => 'mm_ref_stored_access',
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

    Http::assertSent(fn ($request) => $request->url() === config('moneyman.providers.boa_ussd.base_url').'/ussd/push'
        && $request->hasHeader('Authorization', 'Bearer stored-access-token'));
});

it('refreshes token and retries once when initiate returns 401', function (): void {
    Cache::put('boa_ussd.access_token', 'expired-access-token', now()->addMinutes(5));

    Http::fake([
        config('moneyman.providers.boa_ussd.base_url').'/ussd/push/oauth2/token' => Http::response([
            'access_token' => 'new-access-token',
            'refresh_token' => 'new-refresh-token',
        ], 200),
        config('moneyman.providers.boa_ussd.base_url').'/ussd/push' => Http::sequence()
            ->push(['message' => 'Unauthorized'], 401)
            ->push([
                'pushUSSDResult' => [
                    'paymentStatus' => 'PENDING_USER_PIN',
                    'billNumber' => 'mm_ref_retry',
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

    expect($response->isSuccessful())->toBeTrue();

    Http::assertSentCount(3);
    Http::assertSent(fn ($request) => $request->url() === config('moneyman.providers.boa_ussd.base_url').'/ussd/push'
        && $request->hasHeader('Authorization', 'Bearer new-access-token'));
});

it('returns error verify response after retry when api keeps returning 401', function (): void {
    Cache::put('boa_ussd.access_token', 'expired-access-token', now()->addMinutes(5));

    Http::fake([
        config('moneyman.providers.boa_ussd.base_url').'/ussd/push/oauth2/token' => Http::response([
            'access_token' => 'new-access-token',
            'refresh_token' => 'new-refresh-token',
        ], 200),
        config('moneyman.providers.boa_ussd.base_url').'/ussd/push/getStatus*' => Http::response([
            'message' => 'Unauthorized',
        ], 401),
    ]);

    $response = MoneyMan::provider('boa_ussd')->verify('mm_ref_failed_verify');

    expect($response->isSuccessful())->toBeFalse();
    expect($response->transactionId)->toBe('mm_ref_failed_verify');
});
