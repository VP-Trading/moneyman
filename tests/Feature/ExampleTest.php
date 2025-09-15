<?php

declare(strict_types=1);

use Alazark94\MoneyMan\Enums\Provider;
use Alazark94\MoneyMan\MoneyMan;
use Alazark94\MoneyMan\ValueObjects\User;
use Money\Money;

test('example', function (): void {

    $response = MoneyMan::provider(Provider::Telebirr)
        ->initiate(
            money: Money::ETB(30000),
            user: new User(
                firstName: 'Alazar',
                lastName: 'Kassahun',
                email: 'alazar@gmail.com',
                phoneNumber: '0913517005'
            ),
            returnUrl: 'https://vptrading.et',
            reason: 'Test'
        );

    // $response = MoneyMan::provider(Provider::SantimPay)
    //     ->verify('P02r5thwTl');

    dd($response);
});
