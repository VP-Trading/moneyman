<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\SantimPay\Factories;

use Vptrading\MoneyMan\Contracts\Factories\ProviderResponse;
use Vptrading\MoneyMan\Providers\SantimPay\Dtos\PaymentVerifyResponse;

class PaymentVerifyFactory implements ProviderResponse
{
    public static function fromApiResponse(array $response): PaymentVerifyResponse
    {
        return new PaymentVerifyResponse(
            status: strtolower($response['status']),
            message: $response['message'] ?? null,
            transactionId: $response['id'],
            data: $response
        );
    }
}
