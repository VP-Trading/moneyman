<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\Chapa;

use Illuminate\Support\Facades\Http;
use Money\Money;
use Vptrading\MoneyMan\Providers\Chapa\Dtos\PaymentInitiateResponse;
use Vptrading\MoneyMan\Providers\Chapa\Dtos\PaymentRefundResponse;
use Vptrading\MoneyMan\Providers\Chapa\Dtos\PaymentVerifyResponse;
use Vptrading\MoneyMan\Providers\Chapa\Factories\PaymentInitiateFactory;
use Vptrading\MoneyMan\Providers\Chapa\Factories\PaymentRefundFactory;
use Vptrading\MoneyMan\Providers\Chapa\Factories\PaymentVerifyFactory;
use Vptrading\MoneyMan\Providers\Provider;
use Vptrading\MoneyMan\ValueObjects\User;

class Chapa extends Provider
{
    protected string $secretKey;

    protected string $baseUrl;

    public function __construct()
    {
        parent::__construct();

        if (empty(config('moneyman.providers.chapa.secret_key'))) {
            throw new \InvalidArgumentException('Chapa secret key is not set.');
        }

        $this->baseUrl = config('moneyman.providers.chapa.base_url');
        $this->secretKey = config('moneyman.providers.chapa.secret_key');
    }

    #[\Override]
    public function initiate(Money $money, User $user, string $returnUrl, ?string $reason = null, ?array $parameters = []): PaymentInitiateResponse
    {
        $transactionId = config('moneyman.ref_prefix').str()->random(10);
        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/transaction/initialize", [
                'first_name' => $user->firstName,
                'last_name' => $user->lastName,
                'amount' => $this->formatter->format($money),
                'currency' => $money->getCurrency()->getCode(),
                'email' => $user->email,
                'phone_number' => $user->phoneNumber,
                'return_url' => $returnUrl,
                'callback_url' => config('moneyman.providers.chapa.callback_url'),
                'tx_ref' => $transactionId,
                'customization' => $parameters['customization'] ?? [],
            ]);

        $response = $response->json();
        $response['transactionId'] = $transactionId;

        return PaymentInitiateFactory::fromApiResponse($response);
    }

    public function verify(string $transactionId): PaymentVerifyResponse
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transaction/verify/{$transactionId}");

        return PaymentVerifyFactory::fromApiResponse($response->json());
    }

    public function refund(string $transactionId, ?Money $amount = null, ?string $reason = null): PaymentRefundResponse
    {
        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/refund/$transactionId", [
                'amount' => $amount ? $this->formatter->format($amount) : null,
                'reason' => $reason,
            ]);

        return PaymentRefundFactory::fromApiResponse($response->json());
    }
}
