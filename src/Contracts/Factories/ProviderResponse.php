<?php

declare(strict_types=1);

namespace Alazark94\CashierEt\Contracts\Factories;

use Alazark94\CashierEt\Contracts\Responses\TransactionResponse;

interface ProviderResponse
{
    public static function fromApiResponse(array $response): TransactionResponse;
}
