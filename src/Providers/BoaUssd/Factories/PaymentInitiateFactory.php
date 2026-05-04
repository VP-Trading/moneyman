<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\BoaUssd\Factories;

use Vptrading\MoneyMan\Contracts\Factories\ProviderResponse;
use Vptrading\MoneyMan\Providers\BoaUssd\Dtos\PaymentInitiateResponse;

class PaymentInitiateFactory implements ProviderResponse
{
    public static function fromApiResponse(array $response): PaymentInitiateResponse
    {
        $successful = $response['_http_successful'] ?? false;

        if (! $successful) {
            return new PaymentInitiateResponse(
                status: 'error',
                message: $response['pushUSSDResult']['ResultDesc'] ?? 'Unable to initiate BOA USSD payment.',
                transactionId: $response['pushUSSDResult']['billNumber'] ?? null,
            );
        }

        return new PaymentInitiateResponse(
            status: 'success',
            message: $response['pushUSSDResult']['paymentStatus'] ?? null,
            transactionId: $response['pushUSSDResult']['billNumber'] ?? null,
        );
    }
}
