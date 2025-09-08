<?php

declare(strict_types=1);

use Alazark94\MoneyMan\Enums\Provider;
use Alazark94\MoneyMan\MoneyMan;
use Alazark94\MoneyMan\ValueObjects\User;
use Money\Money;

test('example', function (): void {
    // MoneyMan::provider(Provider::Telebirr)
    //     ->initiate(
    //         Money::ETB(10000),
    //         new User(
    //             firstName: 'Alazar',
    //             lastName: 'Kassahun',
    //             email: 'alazar@gmail.com',
    //             phoneNumber: '0913517005'
    //         ),
    //         returnUrl: 'https://vptrading.et'
    //     );

    // $response = MoneyMan::provider(Provider::Telebirr)
    //     ->verify('glRDRSe9U6');

    $response = MoneyMan::provider(Provider::Telebirr)
        ->refund('glRDRSe9U6');

    dd($response);
});
