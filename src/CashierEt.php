<?php

namespace Alazark94\CashierEt;

use Alazark94\CashierEt\Enums\Provider as ProviderEnum;
use Alazark94\CashierEt\Providers\Provider;

class CashierEt
{
    public static function provider(ProviderEnum|string $provider): Provider
    {
        return app(CashierEtManager::class)->resolve($provider);
    }
}
