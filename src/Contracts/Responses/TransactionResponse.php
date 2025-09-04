<?php

declare(strict_types=1);

namespace Alazark94\CashierEt\Contracts\Responses;

interface TransactionResponse
{
    public function isSuccessful(): bool;

    public function getMessage(): ?string;

    public function getTransactionId(): ?string;
}
