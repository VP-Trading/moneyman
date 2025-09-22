<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\Chapa\Factories;

use Vptrading\MoneyMan\Contracts\Factories\ProviderResponse;
use Vptrading\MoneyMan\Providers\Chapa\Dtos\PaymentRefundResponse;

class PaymentRefundFactory implements ProviderResponse
{
    public static function fromApiResponse(array $response): PaymentRefundResponse
    {
        return new PaymentRefundResponse(
            status: $response['status'],
            message: $response['message'],
            data: $response['data'],
            transactionId: $response['data']['tx_ref'] ?? null
        );
    }
}
