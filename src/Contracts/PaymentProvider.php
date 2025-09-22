<?php

declare(strict_types=1);

namespace Alazark94\MoneyMan\Contracts;

use Alazark94\MoneyMan\Contracts\Responses\PaymentInitiateResponse;
use Alazark94\MoneyMan\Contracts\Responses\PaymentRefundResponse;
use Alazark94\MoneyMan\Contracts\Responses\PaymentVerifyResponse;
use Alazark94\MoneyMan\ValueObjects\User;
use Money\Money;

interface PaymentProvider
{
    public function initiate(Money $amount, User $user, string $returnUrl, ?string $reason = null, ?array $parameters = []): PaymentInitiateResponse;

    public function verify(string $transactionId): PaymentVerifyResponse;

    public function refund(string $transactionId, ?Money $amount = null, ?string $reason = null): PaymentRefundResponse;
}
