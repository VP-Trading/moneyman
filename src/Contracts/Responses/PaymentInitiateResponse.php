<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Contracts\Responses;

interface PaymentInitiateResponse extends TransactionResponse
{
    public function getCheckoutUrl(): ?string;
}
