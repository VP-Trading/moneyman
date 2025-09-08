<?php

namespace Alazark94\MoneyMan\Providers\Telebirr;

use Alazark94\MoneyMan\Providers\Provider;
use Alazark94\MoneyMan\Providers\Telebirr\Dtos\PaymentInitiateResponse;
use Alazark94\MoneyMan\Providers\Telebirr\Dtos\PaymentRefundResponse;
use Alazark94\MoneyMan\Providers\Telebirr\Dtos\PaymentVerifyResponse;
use Alazark94\MoneyMan\ValueObjects\User;
use Exception;
use Illuminate\Support\Facades\Http;
use Money\Money;
use phpseclib3\Crypt\PublicKeyLoader;

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

        $order = $this->createOrder($request);

        $rawRequest = $this->createRawRequest($order['biz_content']['prepay_id']);
        // dd($rawRequest);
        $request = [
            'timestamp' => (string) now()->timestamp,
            'nonce_str' => str()->random(32),
            'method' => 'payment.checkout',
            'app_code' => config('moneyman.providers.telebirr.merchant_app_id'),
            'version' => '1.0',
            'biz_content' => [
                'trade_type' => 'WebCheckout',
                'raw_request' => $rawRequest,
            ]
        ];

        // dd(json_encode($request, JSON_UNESCAPED_SLASHES), $this->sign($request));
        $request['sign'] = $this->sign($request);
        $request['sign_type'] = 'SHA256WithRSA';
        // dd($request);

        // dd(json_encode($request, JSON_UNESCAPED_SLASHES));

        $response = Http::withoutVerifying()
            ->withToken(str()->chopStart($this->generateFabricToken(), 'Bearer '))
            ->post(config('moneyman.providers.telebirr.base_url') . '/payment/v1/app/checkout', $request);

        dd($response->json());

        dd('https://developerportal.ethiotelebirr.et:38443/payment/web/paygate/?' . $rawRequest . "&version=1.0&trade_type=Checkout");

        // Http::post('https://developerportal.ethiotelebirr.et:38443/payment/web/paygate?' . $this->createRawRequest('018c19ad503a02749eb9959c115a0f5ff89009') . "&version=1.0&trade_type=Checkout", )
        return new PaymentInitiateResponse('testing', 'testing');
    }

    public function verify(string $transactionId): PaymentVerifyResponse
    {
        $request = [
            'timestamp' => (string) now()->timestamp,
            'nonce_str' => str()->random(32),
            'method' => 'payment.queryorder',
            'app_code' => config('moneyman.providers.telebirr.merchant_app_id'),
            'version' => '1.0',
            'biz_content' => [
                'appid' => config('moneyman.providers.telebirr.merchant_app_id'),
                'merch_code' => config('moneyman.providers.telebirr.short_code'),
                'merch_order_id' => 'glRDRSe9U6'
            ]
        ];

        $request['sign'] = $this->sign($request);
        $request['sign_type'] = 'SHA256WithRSA';

        $response = Http::withoutVerifying()
            ->withToken(str()->chopStart($this->generateFabricToken(), 'Bearer '))
            ->post(config('moneyman.providers.telebirr.base_url') . '/payment/v1/merchant/queryOrder', $request);


        return new PaymentVerifyResponse($response->json('biz_content.order_status'), $response->json('msg'), $response->json(), $response->json('biz_content.merch_order_id'));
    }

    public function refund(string $transactionId, ?Money $amount = null, ?string $reason = null): PaymentRefundResponse
    {
        $request = [
            'timestamp' => (string) now()->timestamp,
            'nonce_str' => str()->random(32),
            'method' => 'payment.refund',
            'app_code' => config('moneyman.providers.telebirr.merchant_app_id'),
            'version' => '1.0',
            'biz_content' => [
                'appid' => config('moneyman.providers.telebirr.merchant_app_id'),
                'refund_request_no' => str()->random(32),
                'merch_code' => config('moneyman.providers.telebirr.short_code'),
                'merch_order_id' => $transactionId,
                'refund_amount' => $amount ? $this->formatter->format($amount) : '0',
                'refund_currency' => $amount ? $amount->getCurrency()->getCode() : 'ETB',
                'refund_reason' => $reason ? $reason : 'Customer Request',
                'notify_url' => config('moneyman.providers.telebirr.callback_url')
            ]
        ];

        $request['sign'] = $this->sign($request);
        $request['sign_type'] = 'SHA256WithRSA';

        $response = Http::withoutVerifying()
            ->withToken(str()->chopStart($this->generateFabricToken(), 'Bearer '))
            ->post(config('moneyman.providers.telebirr.base_url') . '/payment/v1/merchant/refund', $request);

        if (!$response->successful()) {
            throw new \Exception($response->json('errorMsg'));
        }
        return new PaymentRefundResponse($response->json('result'), $response->json('msg'), $response->json(), $response->json('biz_content.merch_order_id'));
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
        $response = Http::withHeader('X-APP-Key', config('moneyman.providers.telebirr.fabric_app_id'))
            ->withToken(str()->chopStart($this->generateFabricToken(), 'Bearer '))
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
            'merch_order_id' =>  str()->random(32),
            'trade_type' => 'WebCheckout',
            'title' => 'Checkout',
            'total_amount' => $this->formatter->format($amount),
            'trans_currency' => $amount->getCurrency()->getCode(),
            'timeout_express' => '120m',
            'callback_info' => 'Test'
        ];

        $request = [
            'timestamp' => (string) now()->timestamp,
            'method' => 'payment.preorder',
            'nonce_str' => str()->random(32),
            'version' => '1.0',
            'biz_content' => $bizContent
        ];

        $request['sign'] = $this->sign($request);
        $request['sign_type'] = 'SHA256WithRSA';

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

        $pairs = [];
        foreach ($filtered as $k => $v) {
            $pairs[] = $k . '=' . $v;
        }
        $stringApplet = implode('&', $pairs);


        $pem = "-----BEGIN PRIVATE KEY-----\n" .
            chunk_split(config('moneyman.providers.telebirr.private_key'), 64, "\n") .
            "-----END PRIVATE KEY-----\n";
        $private = PublicKeyLoader::load($pem, $passphrase);

        $private = $private->withHash('sha256');

        return base64_encode($private->sign($stringApplet));
    }


    private function sortedString($stringApplet)
    {
        $stringExplode = '';
        $sortedArray = explode("&", $stringApplet);
        sort($sortedArray);
        foreach ($sortedArray as $x => $x_value) {
            if ($stringExplode == '') {
                $stringExplode = $x_value;
            } else {
                $stringExplode = $stringExplode . '&' . $x_value;
            }
        }

        return $stringExplode;
    }

    private function createRawRequest(string $prepayId)
    {
        $maps = [
            "appid"      => config('moneyman.providers.telebirr.merchant_app_id'),
            "merch_code" => config('moneyman.providers.telebirr.short_code'),
            "nonce_str"  => str()->random(32),
            "prepay_id"  => $prepayId,
            "timestamp"  => (string) now()->timestamp,
        ];

        $sign = $this->sign($maps);

        $maps['sign'] = $sign;
        $maps['sign_type'] = 'SHA256WithRSA';

        $pairs = [];
        foreach ($maps as $k => $v) {
            $pairs[] = $k . '=' . $v;
        }

        return implode('&', $pairs);
    }
}
