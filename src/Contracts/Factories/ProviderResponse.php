<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Contracts\Factories;

use Vptrading\MoneyMan\Contracts\Responses\TransactionResponse;

interface ProviderResponse
{
    public static function fromApiResponse(array $response): TransactionResponse;
}
