<?php

declare(strict_types=1);

use Vptrading\MoneyMan\Enums\Provider as ProviderEnum;
use Vptrading\MoneyMan\MoneyMan;
use Vptrading\MoneyMan\Providers\Chapa\Chapa;
use Vptrading\MoneyMan\Providers\SantimPay\SantimPay;
use Vptrading\MoneyMan\Providers\Telebirr\Telebirr;

it('resolves providers from enum', function (): void {
    expect(MoneyMan::provider(ProviderEnum::Chapa))->toBeInstanceOf(Chapa::class);
    expect(MoneyMan::provider(ProviderEnum::SantimPay))->toBeInstanceOf(SantimPay::class);
    expect(MoneyMan::provider(ProviderEnum::Telebirr))->toBeInstanceOf(Telebirr::class);
});

it('throws for unsupported provider', function (): void {
    expect(fn () => MoneyMan::provider('unsupported'))
        ->toThrow(InvalidArgumentException::class);
});
