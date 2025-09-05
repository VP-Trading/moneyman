<?php

declare(strict_types=1);

namespace Alazark94\MoneyMan;

use Alazark94\MoneyMan\Enums\Provider as ProviderEnum;
use Alazark94\MoneyMan\Providers\Provider;

class MoneyMan
{
    public static function provider(ProviderEnum|string $provider): Provider
    {
        return app(MoneyManManager::class)->resolve($provider);
    }
}
