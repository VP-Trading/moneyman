<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Contracts;

use Money\Money;
use Vptrading\MoneyMan\Contracts\Responses\PaymentInitiateResponse;
use Vptrading\MoneyMan\Contracts\Responses\PaymentRefundResponse;
use Vptrading\MoneyMan\Contracts\Responses\PaymentVerifyResponse;
use Vptrading\MoneyMan\ValueObjects\User;

interface PaymentProvider
{
    public function initiate(Money $amount, User $user, string $returnUrl, ?string $reason = null, ?array $parameters = []): PaymentInitiateResponse;

    public function verify(string $transactionId): PaymentVerifyResponse;

    public function refund(string $transactionId, ?Money $amount = null, ?string $reason = null): PaymentRefundResponse;
}
