<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan;

use Vptrading\MoneyMan\Enums\Provider as ProviderEnum;
use Vptrading\MoneyMan\Providers\Provider;

class MoneyMan
{
    public static function provider(ProviderEnum|string $provider): Provider
    {
        return app(MoneyManManager::class)->resolve($provider);
    }
}
