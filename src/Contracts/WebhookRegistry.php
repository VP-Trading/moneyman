<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Contracts;

interface WebhookRegistry
{
    public function get(string $provider): WebhookDriver;
}
