<?php

declare(strict_types=1);

namespace Alazark94\MoneyMan\Contracts\Factories;

use Alazark94\MoneyMan\Contracts\Responses\TransactionResponse;

interface ProviderResponse
{
    public static function fromApiResponse(array $response): TransactionResponse;
}
