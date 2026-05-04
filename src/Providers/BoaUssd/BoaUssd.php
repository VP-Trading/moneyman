<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\BoaUssd;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use LogicException;
use Money\Money;
use Override;
use Vptrading\MoneyMan\Contracts\Responses\PaymentInitiateResponse;
use Vptrading\MoneyMan\Contracts\Responses\PaymentRefundResponse;
use Vptrading\MoneyMan\Contracts\Responses\PaymentVerifyResponse;
use Vptrading\MoneyMan\Providers\BoaUssd\Factories\PaymentInitiateFactory;
use Vptrading\MoneyMan\Providers\Provider;
use Vptrading\MoneyMan\ValueObjects\User;

class BoaUssd extends Provider
{
    private const ACCESS_TOKEN_CACHE_KEY = 'boa_ussd.access_token';

    private const REFRESH_TOKEN_CACHE_KEY = 'boa_ussd.refresh_token';

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
        $response = Http::withToken($this->getAccessToken())
            ->withHeader('x-api-key', config('moneyman.providers.boa_ussd.api_key'))
            ->post($this->baseUrl().'/ussd/push', [
                'ID' => config('moneyman.providers.boa_ussd.client_id'),
                'phoneNumber' => $user->phoneNumber,
                'amount' => $this->formatter->format($money),
                'merchantName' => config('moneyman.providers.boa_ussd.merchant_name'),
                'merchantAccount' => config('moneyman.providers.boa_ussd.merchant_account'),
                'billNumber' => config('moneyman.ref_prefix').str()->random(10),
            ]);

        return PaymentInitiateFactory::fromApiResponse(array_merge($response->json(), [
            '_http_successful' => $response->successful(),
        ]));
    }

    #[Override]
    public function verify(string $transactionId): PaymentVerifyResponse
    {
        throw new LogicException("BOA USSD doesn't provide verify functionality yet.");
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
            throw new \RuntimeException($response->json('error_description') ?? 'Failed to refresh BOA USSD token.');
        }

        $accessToken = (string) $response->json('access_token');
        $refreshToken = (string) $response->json('refresh_token');

        if ($accessToken === '' || $refreshToken === '') {
            throw new \RuntimeException('BOA USSD token response is missing access_token or refresh_token.');
        }

        Cache::put(self::ACCESS_TOKEN_CACHE_KEY, $accessToken, now()->addMinutes(25));
        Cache::put(self::REFRESH_TOKEN_CACHE_KEY, $refreshToken, now()->addDays(7));

        return $accessToken;
    }

    private function resolveRefreshToken(): string
    {
        $cachedToken = Cache::get(self::REFRESH_TOKEN_CACHE_KEY);

        if (is_string($cachedToken) && $cachedToken !== '') {
            return $cachedToken;
        }

        $seedToken = (string) config('moneyman.providers.boa_ussd.refresh_token');

        if ($seedToken === '') {
            throw new \RuntimeException('BOA USSD refresh token is not configured.');
        }

        return $seedToken;
    }

    private function baseUrl(): string
    {
        return trim((string) config('moneyman.providers.boa_ussd.base_url'), '/');
    }
}
