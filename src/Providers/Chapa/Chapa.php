<?php

declare(strict_types=1);

namespace Alazark94\MoneyMan\Providers\Chapa;

use Alazark94\MoneyMan\Providers\Chapa\Dtos\PaymentInitiateResponse;
use Alazark94\MoneyMan\Providers\Chapa\Dtos\PaymentRefundResponse;
use Alazark94\MoneyMan\Providers\Chapa\Dtos\PaymentVerifyResponse;
use Alazark94\MoneyMan\Providers\Chapa\Factories\PaymentInitiateFactory;
use Alazark94\MoneyMan\Providers\Chapa\Factories\PaymentRefundFactory;
use Alazark94\MoneyMan\Providers\Chapa\Factories\PaymentVerifyFactory;
use Alazark94\MoneyMan\Providers\Provider;
use Alazark94\MoneyMan\ValueObjects\User;
use Illuminate\Support\Facades\Http;
use Money\Money;

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
    public function initiate(Money $money, User $user, string $returnUrl, ?array $parameters = []): PaymentInitiateResponse
    {
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
                'tx_ref' => config('moneyman.ref_prefix') . str()->random(10),
                'customization' => $parameters['customization'] ?? [],
            ]);

        return PaymentInitiateFactory::fromApiResponse($response->json());
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
