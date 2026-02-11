<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Dtos;

use Vptrading\MoneyMan\Enums\Provider;

class WebhookEvent
{
    public function __construct(
        public Provider $provider,
        public ?string $eventType,
        public string $txRef,
        public ?string $providerReference,
        public string $status,
        public ?float $amount = null, // smallest unit if possible
        public ?string $currency = null,
        public ?float $charge = null, // unix timestamp
        public array $meta = [],
    ) {
        //
    }
}
