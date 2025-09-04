<?php

declare(strict_types=1);

use Alazark94\CashierEt\CashierEt;
use Alazark94\CashierEt\Providers\Chapa\Chapa;
use Alazark94\CashierEt\ValueObjects\User;
use Money\Money;

test('example', function (): void {
    config()->set('cashier-et.chapa.secret_key', 'CHASECK_TEST-ieiAS2tVES55MpNkDOFGKFrUCf9WLKya');
    // $test = CashierEt::provider('chapa')->initiate(Money::ETB(100), new User(
    //     firstName: 'John',
    //     lastName: 'Doe',
    //     email: 'alazar@gmail.com',
    //     phoneNumber: '0912345678'
    // ), 'http://example.com/callback',);

    $test = CashierEt::provider('chapa')
        ->refund('APDpJbbudyX7D', Money::ETB(5000), reason: 'Because i dont need it no more');

    dd($test);
});
