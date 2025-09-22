<?php

declare(strict_types=1);

namespace Alazark94\MoneyMan\Providers\Chapa\Factories;

use Alazark94\MoneyMan\Contracts\Factories\ProviderResponse;
use Alazark94\MoneyMan\Providers\Chapa\Dtos\PaymentInitiateResponse;

class PaymentInitiateFactory implements ProviderResponse
{
    public static function fromApiResponse(array $response): PaymentInitiateResponse
    {
        if (array_key_exists('message', $response)) {
            if (is_string($response['message'])) {
                $message = $response['message'];
            } elseif (is_array($response['message'])) {
                $validationErrors = $response['message'];
            }
        }

        return new PaymentInitiateResponse(
            status: $response['status'] ?? 'error',
            message: $message ?? null,
            transactionId: $response['transactionId'] ?? null,
            checkoutUrl: $response['data']['checkout_url'] ?? null,
            validationErrors: $validationErrors ?? []
        );
    }
}
