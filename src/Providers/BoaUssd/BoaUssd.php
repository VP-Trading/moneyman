<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\BoaUssd;

use Closure;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use LogicException;
use Money\Money;
use Override;
use RuntimeException;
use Vptrading\MoneyMan\Contracts\Responses\PaymentInitiateResponse;
use Vptrading\MoneyMan\Contracts\Responses\PaymentRefundResponse;
use Vptrading\MoneyMan\Contracts\Responses\PaymentVerifyResponse;
use Vptrading\MoneyMan\Models\BoAToken;
use Vptrading\MoneyMan\Providers\BoaUssd\Factories\PaymentInitiateFactory;
use Vptrading\MoneyMan\Providers\BoaUssd\Factories\PaymentVerifyFactory;
use Vptrading\MoneyMan\Providers\Provider;
use Vptrading\MoneyMan\ValueObjects\User;

class BoaUssd extends Provider
{
    private const ACCESS_TOKEN_CACHE_KEY = 'boa_ussd.access_token';

    private const TOKEN_LOCK_KEY = 'boa_ussd.token_refresh_lock';

    public function __construct()
    {
        parent::__construct();

        foreach (config('moneyman.providers.boa_ussd') as $key => $value) {
            if (empty($value)) {
                $formattedKey = ucwords(implode(' ', explode('_', $key)));
                throw new \InvalidArgumentException("BOA USSD $formattedKey is not set.");
            }
        }
    }

    public function initiate(Money $money, User $user, string $returnUrl, ?string $reason = null, ?array $parameters = []): PaymentInitiateResponse
    {
        $response = $this->sendAuthorizedRequest(fn (string $accessToken): Response => Http::withToken($accessToken)
            ->withHeader('x-api-key', config('moneyman.providers.boa_ussd.api_key'))
            ->post($this->baseUrl().'/ussd/push', [
                'ID' => config('moneyman.providers.boa_ussd.client_id'),
                'phoneNumber' => $user->phoneNumber,
                'amount' => $this->formatter->format($money),
                'merchantName' => config('moneyman.providers.boa_ussd.merchant_name'),
                'merchantAccount' => config('moneyman.providers.boa_ussd.merchant_account'),
                'billNumber' => config('moneyman.ref_prefix').str()->random(10),
            ]));

        return PaymentInitiateFactory::fromApiResponse(array_merge($response->json(), [
            '_http_successful' => $response->successful(),
        ]));
    }

    #[Override]
    public function verify(string $transactionId): PaymentVerifyResponse
    {
        $response = $this->sendAuthorizedRequest(fn (string $accessToken): Response => Http::withToken($accessToken)
            ->withHeader('x-api-key', config('moneyman.providers.boa_ussd.api_key'))
            ->withHeader('Content-Type', 'application/json')
            ->get($this->baseUrl().'/ussd/push/getStatus', [
                'reference' => $transactionId,
            ]));

        return PaymentVerifyFactory::fromApiResponse(array_merge($response->json(), [
            '_http_successful' => $response->successful(),
            '_transaction_id' => $transactionId,
        ]));
    }

    #[Override]
    public function refund(string $transactionId, ?Money $amount = null, ?string $reason = null): PaymentRefundResponse
    {
        throw new LogicException("BOA USSD doesn't provide refund functionality yet.");
    }

    private function getAccessToken(): string
    {
        $cachedToken = Cache::get(self::ACCESS_TOKEN_CACHE_KEY);

        if (is_string($cachedToken) && $cachedToken !== '') {
            return $cachedToken;
        }

        return Cache::lock(self::TOKEN_LOCK_KEY, 10)->block(5, function (): string {
            $tokenAfterLock = Cache::get(self::ACCESS_TOKEN_CACHE_KEY);

            if (is_string($tokenAfterLock) && $tokenAfterLock !== '') {
                return $tokenAfterLock;
            }

            $storedToken = $this->storedAccessToken();

            if ($storedToken !== null && $storedToken !== '') {
                Cache::put(self::ACCESS_TOKEN_CACHE_KEY, $storedToken, 7200);

                return $storedToken;
            }

            return $this->refreshTokens();
        });
    }

    private function refreshTokens(): string
    {
        $response = Http::post($this->baseUrl().'/ussd/push/oauth2/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->resolveRefreshToken(),
            'client_secret' => config('moneyman.providers.boa_ussd.client_secret'),
            'client_id' => config('moneyman.providers.boa_ussd.client_id'),
        ]);

        if (! $response->successful()) {
            throw new RuntimeException($response->json('error_description') ?? 'Failed to refresh BOA USSD token.');
        }

        $accessToken = (string) $response->json('access_token');
        $refreshToken = (string) $response->json('refresh_token');

        if ($accessToken === '' || $refreshToken === '') {
            throw new RuntimeException('BOA USSD token response is missing access_token or refresh_token.');
        }

        $this->storeTokens($accessToken, $refreshToken);
        Cache::put(self::ACCESS_TOKEN_CACHE_KEY, $accessToken, 7200);

        return $accessToken;
    }

    private function resolveRefreshToken(): string
    {
        $storedToken = $this->storedRefreshToken();

        if ($storedToken !== null && $storedToken !== '') {
            return $storedToken;
        }

        $seedToken = (string) config('moneyman.providers.boa_ussd.refresh_token');

        if ($seedToken === '') {
            throw new RuntimeException('BOA USSD refresh token is not configured.');
        }

        $this->storeRefreshToken($seedToken);

        return $seedToken;
    }

    private function sendAuthorizedRequest(Closure $request): Response
    {
        $response = $request($this->getAccessToken());

        if ($response->status() !== 401) {
            return $response;
        }

        Cache::forget(self::ACCESS_TOKEN_CACHE_KEY);

        return $request($this->refreshAccessTokenWithLock());
    }

    private function refreshAccessTokenWithLock(): string
    {
        return Cache::lock(self::TOKEN_LOCK_KEY, 10)->block(5, fn (): string => $this->refreshTokens());
    }

    private function storeTokens(string $accessToken, string $refreshToken): void
    {
        $record = BoAToken::query()->first();

        if ($record === null) {
            BoAToken::query()->create([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
            ]);

            return;
        }

        $record->fill([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ])->save();
    }

    private function storeRefreshToken(string $refreshToken): void
    {
        $record = BoAToken::query()->first();

        if ($record === null) {
            BoAToken::query()->create([
                'refresh_token' => $refreshToken,
            ]);

            return;
        }

        $record->fill([
            'refresh_token' => $refreshToken,
        ])->save();
    }

    private function storedAccessToken(): ?string
    {
        $accessToken = BoAToken::query()->value('access_token');

        return is_string($accessToken) && $accessToken !== '' ? $accessToken : null;
    }

    private function storedRefreshToken(): ?string
    {
        $refreshToken = BoAToken::query()->value('refresh_token');

        return is_string($refreshToken) && $refreshToken !== '' ? $refreshToken : null;
    }

    private function baseUrl(): string
    {
        return trim((string) config('moneyman.providers.boa_ussd.base_url'), '/');
    }
}
