<?php

namespace Alazark94\MoneyMan\Providers\Telebirr;

use Alazark94\MoneyMan\Providers\Chapa\Factories\PaymentInitiateFactory;
use Alazark94\MoneyMan\Providers\Provider;
use Alazark94\MoneyMan\Providers\Telebirr\Dtos\PaymentInitiateResponse;
use Alazark94\MoneyMan\Providers\Telebirr\Dtos\PaymentRefundResponse;
use Alazark94\MoneyMan\Providers\Telebirr\Dtos\PaymentVerifyResponse;
use Alazark94\MoneyMan\ValueObjects\User;
use Exception;
use Illuminate\Support\Facades\Http;
use Money\Money;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;

class Telebirr extends Provider
{

    public function __construct()
    {
        parent::__construct();

        foreach (config('moneyman.providers.telebirr') as $key => $value) {
            if (empty($value)) {
                $formattedKey = ucwords(implode(' ', explode('_', $key)));
                throw new \InvalidArgumentException("Telebirr $formattedKey is not set.");
            }
        }
    }

    public function initiate(Money $money, User $user, string $returnUrl, ?array $parameters = []): PaymentInitiateResponse
    {
        $request = $this->prepareRequest(Money::ETB(1000), 'https://vptrading.et');

        dd($this->createOrder($request));
        return new PaymentInitiateResponse('testing', 'testing');
    }

    public function verify(string $transactionId): PaymentVerifyResponse
    {
        return new PaymentVerifyResponse('test', 'test', [], 'test');
    }

    public function refund(string $transactionId, ?Money $amount = null, ?string $reason = null): PaymentRefundResponse
    {
        return new PaymentRefundResponse('test');
    }

    private function generateFabricToken(): string
    {
        $response = Http::withHeader('X-APP-Key', config('moneyman.providers.telebirr.fabric_app_id'))
            ->withoutVerifying()
            ->post(config('moneyman.providers.telebirr.base_url') . '/payment/v1/token', [
                'appSecret' => config('moneyman.providers.telebirr.app_secret')
            ]);

        if (!$response->successful()) {
            throw new Exception(json_encode($response->json()));
        }

        return $response->json('token');
    }

    private function createOrder(array $request): array
    {
        $response = Http::withHeaders([
            'X-APP-Key' => config('moneyman.providers.telebirr.fabric_app_id'),
        ])->withToken(str()->chopStart($this->generateFabricToken(), 'Bearer '))
            ->withoutVerifying()
            ->post(config('moneyman.providers.telebirr.base_url') . '/payment/v1/merchant/preOrder', $request);

        if (!$response->successful()) {
            throw new Exception(json_encode($response->json()));
        }

        return $response->json();
    }

    private function prepareRequest(Money $amount, string $redirectUrl)
    {
        // dd(config('moneyman.providers.telebirr.timeout'));
        $bizContent = [
            'notify_url' => config('moneyman.providers.telebirr.callback_url'),
            'redirect_url' => $redirectUrl,
            'appid' => config('moneyman.providers.telebirr.merchant_app_id'),
            'merch_code' => config('moneyman.providers.telebirr.short_code'),
            'merch_order_id' =>  str()->random(10),
            'trade_type' => 'WebCheckout',
            'title' => 'Checkout',
            'total_amount' => $this->formatter->format($amount),
            'trans_currency' => $amount->getCurrency()->getCode(),
            'timeout_express' => '1m',

        ];

        $request = [
            'timestamp' => (string) now()->timestamp,
            'method' => 'payment.preorder',
            'nonce_str' => str()->random(32),
            'sign_type' => 'SHA256WithRSA',
            'version' => '1.0',
            'biz_content' => $bizContent
        ];

        $request['sign'] = $this->sign($request);

        // dd($request);

        return $request;
    }

    private function sign(array $request, string|bool $passphrase = false): string
    {
        $exclude_fields = ["sign", "sign_type", "header", "refund_info", "openType", "raw_request"];

        $filtered = array_diff_key($request, array_flip($exclude_fields));
        if (isset($filtered['biz_content']) && is_array($filtered['biz_content'])) {
            $filtered = array_merge($filtered, $filtered['biz_content']);
            unset($filtered['biz_content']);
        }
        ksort($filtered);

        // dd($filtered);

        $pem = "-----BEGIN PRIVATE KEY-----\n" .
            chunk_split(config('moneyman.providers.telebirr.private_key'), 64, "\n") .
            "-----END PRIVATE KEY-----\n";

        // dd($pem);

        // 4. Build query string (key=value&key=value)
        $stringApplet = urldecode(http_build_query($filtered));
        // dd($stringApplet);

        $private = PublicKeyLoader::load($pem, $passphrase);

        $private = $private->withPadding(RSA::SIGNATURE_PKCS1)
            ->withMGFHash('sha256')
            ->withHash('sha256');

        return base64_encode($private->sign($stringApplet));
    }
}
