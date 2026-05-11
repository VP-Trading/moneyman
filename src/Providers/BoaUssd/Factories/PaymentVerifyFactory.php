<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\BoaUssd\Factories;

use Vptrading\MoneyMan\Contracts\Factories\ProviderResponse;
use Vptrading\MoneyMan\Providers\BoaUssd\Dtos\PaymentVerifyResponse;

class PaymentVerifyFactory implements ProviderResponse
{
    public static function fromApiResponse(array $response): PaymentVerifyResponse
    {
        $httpSuccessful = (bool) ($response['_http_successful'] ?? false);
        $status = self::resolveStatus($response, $httpSuccessful);
        $message = self::resolveMessage($response, $httpSuccessful);

        return new PaymentVerifyResponse(
            status: $status,
            message: $message,
            data: $response,
            transactionId: $response['reference'] ?? $response['billNumber'] ?? $response['_transaction_id'] ?? null
        );
    }

    private static function resolveStatus(array $response, bool $httpSuccessful): string
    {
        if (! $httpSuccessful) {
            return 'error';
        }

        $statusCandidates = [
            $response['status'] ?? null,
            $response['paymentStatus'] ?? null,
            $response['ResultCode'] ?? null,
            $response['resultCode'] ?? null,
            $response['pushUSSDResult']['paymentStatus'] ?? null,
            $response['pushUSSDResult']['ResultCode'] ?? null,
        ];

        foreach ($statusCandidates as $candidate) {
            if (! is_scalar($candidate)) {
                continue;
            }

            $normalized = strtolower(trim((string) $candidate));

            if ($normalized === '0' || in_array($normalized, ['success', 'successful', 'completed', 'paid'], true)) {
                return 'success';
            }
        }

        return 'error';
    }

    private static function resolveMessage(array $response, bool $httpSuccessful): string
    {
        return (string) (
            $response['message']
            ?? $response['ResultDesc']
            ?? $response['paymentStatus']
            ?? $response['pushUSSDResult']['ResultDesc']
            ?? $response['pushUSSDResult']['paymentStatus']
            ?? ($httpSuccessful ? 'Unable to determine BOA USSD verification status.' : 'BOA USSD verify request failed.')
        );
    }
}
