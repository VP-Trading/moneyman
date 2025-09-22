<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\Telebirr;

use Exception;
use Illuminate\Support\Facades\Http;
use Money\Money;
use phpseclib3\Crypt\PublicKeyLoader;
use Vptrading\MoneyMan\Providers\Provider;
use Vptrading\MoneyMan\Providers\Telebirr\Dtos\PaymentInitiateResponse;
use Vptrading\MoneyMan\Providers\Telebirr\Dtos\PaymentRefundResponse;
use Vptrading\MoneyMan\Providers\Telebirr\Dtos\PaymentVerifyResponse;
use Vptrading\MoneyMan\ValueObjects\User;

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

    public function initiate(Money $money, User $user, string $returnUrl, ?string $reason = null, ?array $parameters = []): PaymentInitiateResponse
    {
        try {

            if (! $reason) {
                throw new \InvalidArgumentException('The product name parameter is required when using telebirr as a provider.');
            }

            if ($money->getCurrency()->getCode() !== 'ETB') {
                throw new \InvalidArgumentException('The currency must be ETB when using telebirr as a provider.');
            }

            $request = $this->prepareRequest($money, $reason, $returnUrl);

            $order = $this->createOrder($request);

            $rawRequest = $this->createRawRequest($order['biz_content']['prepay_id']);
        } catch (Exception $e) {
            return new PaymentInitiateResponse('error', $e->getMessage());
        }

        return new PaymentInitiateResponse('success', checkoutUrl: config('moneyman.providers.telebirr.web_base_url').'?'.$rawRequest.'&version=1.0&trade_type=Checkout', message: '', transactionId: $order['biz_content']['merch_order_id']);
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
                'merch_order_id' => 'glRDRSe9U6',
            ],
        ];

        $request['sign'] = $this->sign($request);
        $request['sign_type'] = 'SHA256WithRSA';

        $response = Http::withoutVerifying()
            ->withToken(str()->chopStart($this->generateFabricToken(), 'Bearer '))
            ->post(config('moneyman.providers.telebirr.base_url').'/payment/v1/merchant/queryOrder', $request);

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
                'notify_url' => config('moneyman.providers.telebirr.callback_url'),
            ],
        ];

        $request['sign'] = $this->sign($request);
        $request['sign_type'] = 'SHA256WithRSA';

        $response = Http::withoutVerifying()
            ->withToken(str()->chopStart($this->generateFabricToken(), 'Bearer '))
            ->post(config('moneyman.providers.telebirr.base_url').'/payment/v1/merchant/refund', $request);

        if (! $response->successful()) {
            throw new \Exception($response->json('errorMsg'));
        }

        return new PaymentRefundResponse($response->json('result'), $response->json('msg'), $response->json(), $response->json('biz_content.merch_order_id'));
    }

    private function generateFabricToken(): string
    {
        $response = Http::withHeader('X-APP-Key', config('moneyman.providers.telebirr.fabric_app_id'))
            ->withoutVerifying()
            ->post(config('moneyman.providers.telebirr.base_url').'/payment/v1/token', [
                'appSecret' => config('moneyman.providers.telebirr.app_secret'),
            ]);

        if (! $response->successful()) {
            throw new Exception(json_encode($response->json()));
        }

        return $response->json('token');
    }

    private function createOrder(array $request): array
    {
        $response = Http::withHeader('X-APP-Key', config('moneyman.providers.telebirr.fabric_app_id'))
            ->withToken(str()->chopStart($this->generateFabricToken(), 'Bearer '))
            ->withoutVerifying()
            ->post(config('moneyman.providers.telebirr.base_url').'/payment/v1/merchant/preOrder', $request);

        if (! $response->successful()) {
            throw new Exception(json_encode($response->json()));
        }

        return $response->json();
    }

    private function prepareRequest(Money $amount, string $reason, string $redirectUrl)
    {
        $bizContent = [
            'notify_url' => config('moneyman.providers.telebirr.callback_url'),
            'redirect_url' => $redirectUrl,
            'appid' => config('moneyman.providers.telebirr.merchant_app_id'),
            'merch_code' => config('moneyman.providers.telebirr.short_code'),
            'merch_order_id' => implode('', explode('_', config('moneyman.ref_prefix'))).str()->random(10),
            'trade_type' => 'WebCheckout',
            'title' => $reason,
            'total_amount' => $this->formatter->format($amount),
            'trans_currency' => $amount->getCurrency()->getCode(),
            'timeout_express' => (string) config('moneyman.providers.telebirr.timeout').'m',
            'payee_identifier' => config('moneyman.providers.telebirr.short_code'),
            'payee_identifier_type' => '04',
            'payee_type' => '5000',
        ];

        $request = [
            'timestamp' => (string) now()->timestamp,
            'method' => 'payment.preorder',
            'nonce_str' => str()->random(32),
            'version' => '1.0',
            'biz_content' => $bizContent,
        ];

        $request['sign'] = $this->sign($request);
        $request['sign_type'] = 'SHA256WithRSA';

        return $request;
    }

    private function sign(array $request, string|bool $passphrase = false): string
    {
        $exclude_fields = ['sign', 'sign_type', 'header', 'refund_info', 'openType', 'raw_request'];

        $filtered = array_diff_key($request, array_flip($exclude_fields));
        if (isset($filtered['biz_content']) && is_array($filtered['biz_content'])) {
            $filtered = array_merge($filtered, $filtered['biz_content']);
            unset($filtered['biz_content']);
        }
        ksort($filtered);

        $pairs = [];
        foreach ($filtered as $k => $v) {
            $pairs[] = $k.'='.$v;
        }
        $stringApplet = implode('&', $pairs);

        $pem = "-----BEGIN PRIVATE KEY-----\n".
            chunk_split(config('moneyman.providers.telebirr.private_key'), 64, "\n").
            "-----END PRIVATE KEY-----\n";
        $private = PublicKeyLoader::load($pem, $passphrase);

        $private = $private->withHash('sha256');

        /**
         * @ignore undefined method ignore
         */
        return base64_encode($private->sign($stringApplet));
    }

    private function createRawRequest(string $prepayId)
    {
        $maps = [
            'appid' => config('moneyman.providers.telebirr.merchant_app_id'),
            'merch_code' => config('moneyman.providers.telebirr.short_code'),
            'nonce_str' => str()->random(32),
            'prepay_id' => $prepayId,
            'timestamp' => (string) now()->timestamp,
            'sign_type' => 'SHA256WithRSA',
        ];

        $sign = $this->sign($maps);
        $maps['sign'] = $sign;

        $pairs = [];
        foreach ($maps as $k => $v) {
            $pairs[] = $k.'='.$v;
        }

        return implode('&', $pairs);
    }
}
