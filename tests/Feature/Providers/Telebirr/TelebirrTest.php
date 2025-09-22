<?php

declare(strict_types=1);

use Alazark94\MoneyMan\MoneyMan;
use Alazark94\MoneyMan\ValueObjects\User;
use Illuminate\Support\Facades\Http;
use Money\Money;

it('initiates a transaction', function (): void {
    Http::fake([
        config('moneyman.providers.telebirr.base_url').'/payment/v1/token' => Http::response(
            json_decode('{
                            "effectiveDate": "20221101132422",
                            "expirationDate": "20221101142422",
                            "token": "Bearer 94cc42be4412696d754508c06ca1db20"
                        }', true)
        ),
        config('moneyman.providers.telebirr.base_url').'/payment/v1/merchant/preOrder' => Http::response(
            json_decode('{"result": "SUCCESS","code": "0","msg": "success","nonce_str": "97fe4ae0c0604854a749fbf2cc1cc712","sign": "Eo4Bvwx9rpaWAO+iYzaaXHoWBWbYcCGnVZMEcG5TPb8w...","sign_type": "SHA256WithRSA","biz_content": {"merch_order_id": "1705460512562","prepay_id": "080075a4e3213924de2b3b84ad3cac0a6a6001"}}', true)
        ),
    ]);
    $response = MoneyMan::provider('telebirr')->initiate(
        Money::ETB(100),
        new User(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            phoneNumber: '1234567890'
        ),
        'https://example.com/return',
        reason: 'Payment'
    );

    expect($response->status)->toBe('success');
    expect($response->checkoutUrl)->toBeString();
    expect($response->transactionId)->toBeString();
});

it('throws invalid argument exception if secret key is not set', function (): void {
    config()->set('moneyman.providers.telebirr.merchant_app_id', null);

    expect(fn () => MoneyMan::provider('telebirr')->initiate(
        Money::ETB(100),
        new User(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            phoneNumber: '1234567890'
        ),
        'https://example.com/return'
    ))->toThrow(\InvalidArgumentException::class);
});

it('verifies payments', function (): void {

    Http::fake([
        config('moneyman.providers.telebirr.base_url').'/payment/v1/token' => Http::response(
            json_decode('{
                            "effectiveDate": "20221101132422",
                            "expirationDate": "20221101142422",
                            "token": "Bearer 94cc42be4412696d754508c06ca1db20"
                        }', true)
        ),
        config('moneyman.providers.telebirr.base_url').'/payment/v1/merchant/queryOrder' => Http::response(
            json_decode('{"result":"SUCCESS","code":"0","msg":"success","nonce_str":"2d033e66c0214e9aae363369d7ef7d22","sign":"YJcN8Ob7jz6bbLuzWHHrmbHcw\/spqrsUBptjZLbHuoOR3CHcJ\/PqrS0IA6IWBVmlTQSYqAkc9otBl1yXzujDS5F99V7I8tLVlMquX2CWEvdztmnpOVSc4y1CPqXZ2CvP3HHTWTiuy9CD2T3sf5J3WD\/KjxMS8CxhzpDonrZmMTHz4TBRgCfegKo8OiBXQ3fyAH9oW4XND878V5mBOm15YVYCk84N+HRHiwLZcoeJvy2Oh+iz9QxYXAkA846902j3QKTA2+McS\/DgpqsN1SrphdH7H\/e8I9Lzme+mRzxo50axmAeMNmveTxD+kq4\/SWYxqTs+CXhVbZz0sXZPEtf\/iw\/pjcQ6M49NDtqOVL2\/IVmwFCunRwFAKB7vuCZHKDFKSE4MbIupjDg87nFsOk0a6B9EkIM8gxLug9+SleZecFGJPvW\/t8DnheoKXe9Au0mzEwiUY6YTZA4Qz6Ul9bHFhUnSLyK8TwKdCWee7bvmxC3X4Fejb5dZoAHrI+N8RbEAHNoP5j\/BlUnoerA6GnLmun4P2DRpEgdWmw9BGOdrzsRSVpK3XYEtAUVGIqApW7evtfyxBzMGbX63YYR\/ba2XgOxBW1jxq2GZJ3xYUH3ewCZy1i7V\/BlypZwrgPGns9RF+Nwqgsbw365zFtk7U88mEWR+NZRXzo7Bvxm7nus8L5g=","sign_type":"SHA256WithRSA","biz_content":{"merch_order_id":"glRDRSe9U6","order_status":"PAY_FAILED","payment_order_id":null,"trans_time":null,"trans_currency":null,"total_amount":null}}', true)
        ),
    ]);
    $response = MoneyMan::provider('telebirr')->verify('tx-ref');
    expect($response->status)->toBe('PAY_FAILED');
});

it('can refund transactions', function (): void {
    Http::fake([
        config('moneyman.providers.telebirr.base_url').'/payment/v1/token' => Http::response(
            json_decode('{
                            "effectiveDate": "20221101132422",
                            "expirationDate": "20221101142422",
                            "token": "Bearer 94cc42be4412696d754508c06ca1db20"
                        }', true)
        ),
        config('moneyman.providers.telebirr.base_url').'/payment/v1/merchant/refund' => Http::response(
            json_decode('{"errorCode":"60320021","errorMsg":"The original order status has not been completed, and related operations such as refunds cannot be performed.","result":"FAIL","code":"60320021","msg":"The original order status has not been completed, and related operations such as refunds cannot be performed.","nonce_str":"62dc591dc3cc4e14baa0a197edf31d2d","sign":"IRoWe365muwYxXBhSmAHUtAZ2SeTuLkVb\/VhBMnG9S4yXUL9kTdiuz5fGDTbidgT2bEWRnz7YO0kTI9xMa15Xvz6RSXYyMV+masWM1+koRtVyRr\/NiK6gDLEGhNMCdWvzrkVlnKGEsYGah7V+D2jkLr0e1faaJiLB6EzpVjLR3BGxdWBUa0cgb60biqGXJCpbkh+ojbA\/feleF96qMNFOvwz6fF9WiEF97+ZsXuSupq\/WM4XBWoRJ7edsur9+SPe7xKfm+oTXUJT5LVa1Keg3kgU8EQXOd2ZVlY+3dMpeUf+9V3MTs4hFqnRB6NNR4Fm8jDhKxMEmWXEZb93aKKCuQMyHYsu8O3lcDA6OsWZM\/1T6e\/A+3rYVCs+QjuM5pJgRvZAA1iu+vDij\/6aiFDdNU9YE\/C8cwSD1sG\/C4yzte7Sz6vTqlsmtIo6sREWuiqanNqqzWHd19SS7xADK7WkO0dOJVHR6gaQ2F8ELKYOs7i2\/KSW+6e6IXctzENG3QOiwES14IifsE34JAk64Tm4gImz5qs8iQw6tgPzPMxuJXwDQr8bCBYJ4wtLSC1UyNkEzdA5HPgfyxKwpUY+m3OYi+PwBjA8X6oyJXfy40FAUCTMT2C7iIop59nbXW72g7i1hdJcMDe1H\/8WkQ0Zj56VRQcF5ErTv482mOIxO4faiNw=","sign_type":"SHA256WithRSA","biz_content":{"merch_code":null,"merch_order_id":null,"trans_order_id":null,"refund_order_id":null,"refund_amount":null,"refund_currency":null,"refund_time":null}}', true)
        ),
    ]);
    $response = MoneyMan::provider('telebirr')->refund('tx-ref');

    expect($response->status)->toBe('FAIL');
});
