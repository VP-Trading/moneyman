<?php

declare(strict_types=1);

namespace Alazark94\CashierEt\Providers\Chapa\Factories;

use Alazark94\CashierEt\Contracts\Factories\ProviderResponse;
use Alazark94\CashierEt\Providers\Chapa\Dtos\PaymentVerifyResponse;

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
