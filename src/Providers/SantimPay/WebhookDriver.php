<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\SantimPay;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Vptrading\MoneyMan\Contracts\WebhookDriver as WebhookDriverInterface;
use Vptrading\MoneyMan\Exceptions\InvalidSignatureException;

class WebhookDriver implements WebhookDriverInterface
{
    public function verify(Request $request): bool
    {
        try {
            JWT::decode($request->header('signed-token'), new Key("-----BEGIN PUBLIC KEY-----\n".config('moneyman.providers.santimpay.public_key')."\n-----END PUBLIC KEY-----\n", 'ES256'));
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function parse(Request $request): WebhookEvent
    {
        if (! $this->verify($request)) {
            throw new InvalidSignatureException('Webhook signature invalid');
        }

        $content = json_decode($request->getContent(), true);

        return new WebhookEvent($content);
    }
}
