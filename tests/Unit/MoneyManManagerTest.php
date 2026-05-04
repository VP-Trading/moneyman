<?php

declare(strict_types=1);

use Vptrading\MoneyMan\Enums\Provider as ProviderEnum;
use Vptrading\MoneyMan\MoneyMan;
use Vptrading\MoneyMan\Providers\BoaUssd\BoaUssd;
use Vptrading\MoneyMan\Providers\Chapa\Chapa;
use Vptrading\MoneyMan\Providers\SantimPay\SantimPay;
use Vptrading\MoneyMan\Providers\Telebirr\Telebirr;

it('resolves providers from enum', function (): void {
    config()->set('moneyman.providers.boa_ussd.base_url', 'https://api.boa.test');
    config()->set('moneyman.providers.boa_ussd.client_id', 'test-client-id');
    config()->set('moneyman.providers.boa_ussd.client_secret', 'test-client-secret');
    config()->set('moneyman.providers.boa_ussd.refresh_token', 'test-refresh-token');
    config()->set('moneyman.providers.boa_ussd.merchant_name', 'Test Merchant');
    config()->set('moneyman.providers.boa_ussd.merchant_account', '1234567890');
    config()->set('moneyman.providers.boa_ussd.api_key', 'test-api-key');

    expect(MoneyMan::provider(ProviderEnum::Chapa))->toBeInstanceOf(Chapa::class);
    expect(MoneyMan::provider(ProviderEnum::SantimPay))->toBeInstanceOf(SantimPay::class);
    expect(MoneyMan::provider(ProviderEnum::Telebirr))->toBeInstanceOf(Telebirr::class);
    expect(MoneyMan::provider(ProviderEnum::BoaUssd))->toBeInstanceOf(BoaUssd::class);
});

it('throws for unsupported provider', function (): void {
    expect(fn () => MoneyMan::provider('unsupported'))
        ->toThrow(InvalidArgumentException::class);
});
