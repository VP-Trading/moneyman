<?php

declare(strict_types=1);

namespace Alazark94\CashierEt\Providers\Chapa\Factories;

use Alazark94\CashierEt\Contracts\Factories\ProviderResponse;
use Alazark94\CashierEt\Providers\Chapa\Dtos\PaymentRefundResponse;

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
