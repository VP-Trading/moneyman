<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\SantimPay\Factories;

use Vptrading\MoneyMan\Contracts\Factories\ProviderResponse;
use Vptrading\MoneyMan\Providers\SantimPay\Dtos\PaymentInitiateResponse;

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
