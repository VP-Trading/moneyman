<?php

namespace Alazark94\MoneyMan\Providers\SantimPay\Factories;

use Alazark94\MoneyMan\Contracts\Factories\ProviderResponse;
use Alazark94\MoneyMan\Contracts\Responses\TransactionResponse;
use Alazark94\MoneyMan\Providers\SantimPay\Dtos\PaymentVerifyResponse;

class PaymentVerifyFactory implements ProviderResponse
{
    public static function fromApiResponse(array $response): TransactionResponse
    {
        return new PaymentVerifyResponse(
            status: strtolower($response['status']),
            message: $response['message'] ?? null,
            transactionId: $response['id'],
            data: $response
        );
    }
}
