<?php

declare(strict_types=1);

namespace Alazark94\MoneyMan\Providers\SantimPay\Factories;

use Alazark94\MoneyMan\Contracts\Factories\ProviderResponse;
use Alazark94\MoneyMan\Providers\SantimPay\Dtos\PaymentInitiateResponse;

class PaymentInitiateFactory implements ProviderResponse
{
    public static function fromApiResponse(array $response): PaymentInitiateResponse
    {
        if (array_key_exists('reason', $response)) {
            $message = $response['reason'];
        }

        return new PaymentInitiateResponse(
            status: array_key_exists('url', $response) ? 'success' : 'error',
            message: $message ?? null,
            transactionId: $response['transactionId'],
            checkoutUrl: $response['url'] ?? null,
            validationErrors: []
        );
    }
}
