<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers;

use Vptrading\MoneyMan\Contracts\WebhookDriver;
use Vptrading\MoneyMan\Contracts\WebhookRegistry as WebhookRegistryContract;

class WebhookRegistry implements WebhookRegistryContract
{
    public function __construct(protected array $map)
    {
        //
    }

    public function get(string $provider): WebhookDriver
    {
        $provider = strtolower($provider);

        return app($this->map[$provider]);
    }
}
