<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\Chapa\Factories;

use Vptrading\MoneyMan\Contracts\Factories\ProviderResponse;
use Vptrading\MoneyMan\Providers\Chapa\Dtos\PaymentVerifyResponse;

class PaymentVerifyFactory implements ProviderResponse
{
    public static function fromApiResponse(array $response): PaymentVerifyResponse
    {
        return new PaymentVerifyResponse(
            status: $response['status'] ?? 'error',
            message: $response['message'] ?? null,
            data: $response['data'] ?? [],
            transactionId: $response['data']['tx_ref']
        );
    }
}
