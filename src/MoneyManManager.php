<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan;

use Vptrading\MoneyMan\Enums\Provider as ProviderEnum;
use Vptrading\MoneyMan\Providers\Chapa\Chapa;
use Vptrading\MoneyMan\Providers\Provider;
use Vptrading\MoneyMan\Providers\SantimPay\SantimPay;
use Vptrading\MoneyMan\Providers\Telebirr\Telebirr;

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

    protected function createSantimPayProvider(): Provider
    {
        return new SantimPay;
    }
}
