<?php

declare(strict_types=1);

namespace Alazark94\MoneyMan;

use Alazark94\MoneyMan\Enums\Provider as ProviderEnum;
use Alazark94\MoneyMan\Providers\Chapa\Chapa;
use Alazark94\MoneyMan\Providers\Provider;
use Alazark94\MoneyMan\Providers\Telebirr\Telebirr;

class MoneyManManager
{
    public function resolve(ProviderEnum|string $provider): Provider
    {
        $providerName = $this->resolveName($provider);

        $factory = sprintf('create%sProvider', ucfirst($providerName));

        if (method_exists($this, $factory)) {
            return $this->$factory();
        }

        throw new \InvalidArgumentException("Payment provider [$providerName] is not supported.");
    }

    protected function resolveName(ProviderEnum|string $provider): string
    {
        if ($provider instanceof ProviderEnum) {
            return $provider->value;
        }

        return strtolower($provider);
    }

    protected function createChapaProvider(): Provider
    {
        return new Chapa;
    }

    protected function createTelebirrProvider(): Provider
    {
        return new Telebirr;
    }
}
