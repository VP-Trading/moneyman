<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\SantimPay;

use Exception;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use LogicException;
use Money\Money;
use Vptrading\MoneyMan\Contracts\Responses\PaymentInitiateResponse;
use Vptrading\MoneyMan\Contracts\Responses\PaymentRefundResponse;
use Vptrading\MoneyMan\Contracts\Responses\PaymentVerifyResponse;
use Vptrading\MoneyMan\Providers\Provider;
use Vptrading\MoneyMan\Providers\SantimPay\Dtos\PaymentInitiateResponse as DtosPaymentInitiateResponse;
use Vptrading\MoneyMan\Providers\SantimPay\Factories\PaymentInitiateFactory;
use Vptrading\MoneyMan\Providers\SantimPay\Factories\PaymentVerifyFactory;
use Vptrading\MoneyMan\ValueObjects\User;

class SantimPay extends Provider
{
    public function __construct()
    {
        parent::__construct();

        foreach (config('moneyman.providers.santimpay') as $key => $value) {
            if ($key !== 'token' && empty($value)) {
                $formattedKey = ucwords(implode(' ', explode('_', $key)));
                throw new \InvalidArgumentException("SantimPay $formattedKey is not set.");
            }
        }
    }

    public function initiate(Money $money, User $user, string $returnUrl, ?string $reason = null, ?array $parameters = []): PaymentInitiateResponse
    {
        try {
            if (! $reason) {
                throw new \InvalidArgumentException('The reason parameter is required when using SantimPay as a provider.');
            }

            $data = [
                'amount' => $this->formatter->format($money),
                'paymentReason' => $reason,
                'merchantId' => config('moneyman.providers.santimpay.merchant_id'),
                'generated' => time(),
            ];

            $token = $this->sign($data);

            $transactionId = config('moneyman.ref_prefix').str()->random(10);

            $body = array_merge($data, [
                'id' => $transactionId,
                'reason' => $reason,
                'signedToken' => $token,
                'successRedirectUrl' => $returnUrl,
                'failureRedirectUrl' => $returnUrl,
                'notifyUrl' => config('moneyman.providers.santimpay.callback_url'),
            ]);

            $response = Http::withToken($token)
                ->post(
                    config('moneyman.providers.santimpay.base_url').'/initiate-payment',
                    $body
                );
            $response = $response->json();
            $response['transactionId'] = $transactionId;
        } catch (Exception $e) {
            return new DtosPaymentInitiateResponse('error', $e->getMessage());
        }

        return PaymentInitiateFactory::fromApiResponse($response);
    }

    public function verify(string $transactionId): PaymentVerifyResponse
    {
        $request = [
            'id' => $transactionId,
            'merId' => config('moneyman.providers.santimpay.merchant_id'),
            'generated' => now()->timestamp,
        ];

        $payload = [
            'id' => $transactionId,
            'merchantId' => config('moneyman.providers.santimpay.merchant_id'),
            'signedToken' => $this->sign($request),
        ];

        $response = Http::post(config('moneyman.providers.santimpay.base_url').'/fetch-transaction-status', $payload);

        return PaymentVerifyFactory::fromApiResponse($response->json());
    }

    public function refund(string $transactionId, ?Money $amount = null, ?string $reason = null): PaymentRefundResponse
    {
        throw new LogicException("SantimPay doesn't provide refund functionality yet.");
    }

    private function sign(array $data): string
    {
        $privateKey = "-----BEGIN EC PRIVATE KEY-----\n".config('moneyman.providers.santimpay.private_key')."\n-----END EC PRIVATE KEY-----\n";
        $values = explode('\\n', $privateKey);

        $privateKey = implode("\n", $values);

        return JWT::encode($data, $privateKey, 'ES256');
    }
}
