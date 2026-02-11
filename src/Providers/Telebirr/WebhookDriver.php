<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\Telebirr;

use Illuminate\Http\Request;
use Vptrading\MoneyMan\Contracts\WebhookDriver as WebhookDriverInterface;
use Vptrading\MoneyMan\Exceptions\InvalidSignatureException;

class WebhookDriver implements WebhookDriverInterface
{
    public function verify(Request $request): bool
    {
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
