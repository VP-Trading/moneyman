<?php

namespace Alazark94\MoneyMan\Providers\SantimPay;

use Alazark94\MoneyMan\Contracts\Responses\PaymentInitiateResponse;
use Alazark94\MoneyMan\Contracts\Responses\PaymentRefundResponse;
use Alazark94\MoneyMan\Contracts\Responses\PaymentVerifyResponse;
use Alazark94\MoneyMan\Providers\Provider;
use Alazark94\MoneyMan\Providers\SantimPay\Factories\PaymentInitiateFactory;
use Money\Money;
use Alazark94\MoneyMan\ValueObjects\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;

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

    public function initiate(Money $money, User $user, string $returnUrl, ?array $parameters = []): PaymentInitiateResponse
    {

        $data = [
            "amount" =>  $this->formatter->format($money),
            "paymentReason" => 'Goods',
            "merchantId" => config('moneyman.providers.santimpay.merchant_id'),
            "generated" => time()
        ];

        $token = $this->sign($data);

        $body = array_merge($data, [
            'id' => str()->random(10),
            'reason' => 'Goods',
            'signedToken' => $token,
            'successRedirectUrl' => $returnUrl,
            'failureRedirectUrl' => $returnUrl,
            'notifyUrl' => config('moneyman.providers.santimpay.callback_url')
        ]);

        $response = Http::withToken($token)
            ->post(
                config('moneyman.providers.santimpay.base_url') . '/initiate-payment',
                $body
            );

        return PaymentInitiateFactory::fromApiResponse($response->json());
    }

    public function verify(string $transactionId): PaymentVerifyResponse
    {
        // 
    }

    public function refund(string $transactionId, ?Money $amount = null, ?string $reason = null): PaymentRefundResponse
    {
        // 
    }

    private function sign(array $data): string
    {
        $privateKey = "-----BEGIN EC PRIVATE KEY-----\n" . config('moneyman.providers.santimpay.private_key') . "\n-----END EC PRIVATE KEY-----\n";
        $values = explode("\\n", $privateKey);

        $privateKey = implode("\n", $values);

        return JWT::encode($data, $privateKey, 'ES256');
    }
}
