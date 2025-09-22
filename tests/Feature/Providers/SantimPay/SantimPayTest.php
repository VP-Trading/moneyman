<?php

use Alazark94\MoneyMan\MoneyMan;
use Alazark94\MoneyMan\ValueObjects\User;
use Illuminate\Support\Facades\Http;
use Money\Money;

it('initiates a transaction', function () {
    Http::fake(function () {
        return Http::response(
            json_decode(
                '{
                    "url": "https://services.santimpay.com/?data=eyJhbGciOiJFUzIlNiIsInR5cCI6IkpXVCJ9.eyJ0eG5JZCI6ImMwMGUxMzZjLTQ1MDEŁNGI3Yi04MjkzLTU4M2ExODc3YmNmYiIsImllcklkIjoiZmMyMGNkYjgtNjUzZC00YmViLWFlMWItZTclYTFkODQ3ZjQlIiwibWVyTmFtZSI6IlNhbnRpbXBheSBkaXNwdXRLIFBMQyIsImFkZHJlc3MiOiJBQSIsImFtb3VudCI6IjAu0TkiLCJjdXJyZW5jeSI6IkVUQiIsInJlYXNvbi16InBheW1lbnQiLCJjb21taXNzaW9uQW1vdW50IjoiMC4wMTElIiwidG90YWxBbW9lbnQiOiIxLjAwIiwicGhvbmV0dW1iZXIiOiIrMjUxOTMyMTE4OTI5IiwiZXhwIjoxNzEzODcwOTU4LCJpc3MiOiJzZXJ2aWNlcy5zYW50aW1wYXkuY29tIn0.1ECD5_hflMuNMHdorkYgwqQWgHJmQj-mz_EBj21Gorrv1W2q1WvgRpNAmr-jNP1G3NuiFZCohnE_2W3_qVX8w&m=true"
                }',
                true
            ),
            200
        );
    });

    $response = MoneyMan::provider('santimpay')->initiate(
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
    config()->set('moneyman.providers.santimpay.secret_key', null);

    expect(fn() => MoneyMan::provider('santimpay')->initiate(
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
    $tx_ref = 'vp_chapa_' . str()->random(10);

    Http::fake(function () use ($tx_ref) {
        return Http::response(
            json_decode(
                '{
                    "id": "123331d4esdld-88ce-43e8-2a50-9e783a85a",
                    "created_at": "2024-05-08T14:32:39.591722",
                    "updated_at": "2024-05-08T14:32:39.591722",
                    "thirdPartyId": "' . $tx_ref . '",
                    "transactionType": "",
                    "merId": "fc20cdb8-653d-4beb-aelb-e75a1d847f45",
                    "merName": "Santimpay dispute PLC",
                    "address": "AA",
                    "amount": "0.9885",
                    "commission": "0.0115",
                    "totalAmount": "1",
                    "currency": "ETB",
                    "reason": "payment",
                    "msisan": "+251989603997",
                    "accountNumber": "",
                    "clientReference": "",
                    "paymentvia": "",
                    "refId": "",
                    "successRedirectUrl": "https://santimpay.com",
                    "failureRedirectUrl": "https://santimpay.com",
                    "cancelRedirectUrl": "https://santimpay.com",
                    "commissionAmountInPercent": "0.0115",
                    "providerCommissionAmountInPercent": "0",
                    "commissionFromCustomer": false,
                    "message": "",
                    "status": "PENDING",
                    "StatusReason": "",
                    "ReceiverWalletID": ""
                }',
                true
            ),
            200
        );
    });
    $response = MoneyMan::provider('santimpay')->verify($tx_ref);

    expect($response->status)->toBe('pending');
    expect($response->data['thirdPartyId'])->toBe($tx_ref);
});

it('can refund transactions', function (): void {

    expect(fn() => MoneyMan::provider('santimpay')->refund('tx_no', Money::ETB(10000), 'Customer requested refund'))
        ->toThrow(LogicException::class);
});
