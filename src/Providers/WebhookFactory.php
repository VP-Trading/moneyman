<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers;

use InvalidArgumentException;
use Vptrading\MoneyMan\Contracts\WebhookEvent;
use Vptrading\MoneyMan\Providers\Chapa\WebhookEvent as ChapaWebhookEvent;
use Vptrading\MoneyMan\Providers\SantimPay\WebhookEvent as SantimPayWebhookEvent;
use Vptrading\MoneyMan\Providers\Telebirr\WebhookEvent as TelebirrWebhookEvent;

final class WebhookFactory
{
    public function make(string $provider, array $payload): WebhookEvent
    {
        return match ($provider) {
            'chapa' => new ChapaWebhookEvent($payload),
            'telebirr' => new TelebirrWebhookEvent($payload),
            'santimpay' => new SantimPayWebhookEvent($payload),
            default => throw new InvalidArgumentException('Invalid provider given')
        };
    }
}
