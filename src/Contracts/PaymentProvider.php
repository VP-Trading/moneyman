<?php

declare(strict_types=1);

namespace Alazark94\CashierEt\Contracts;

use Alazark94\CashierEt\Contracts\Responses\PaymentInitiateResponse;
use Alazark94\CashierEt\Contracts\Responses\PaymentRefundResponse;
use Alazark94\CashierEt\Contracts\Responses\PaymentVerifyResponse;
use Alazark94\CashierEt\ValueObjects\User;
use Money\Money;

interface PaymentProvider
{
    public function initiate(Money $amount, User $user, string $returnUrl, ?array $parameters = []): PaymentInitiateResponse;

    public function verify(string $transactionId): PaymentVerifyResponse;

    public function refund(string $transactionId, ?Money $amount = null, ?string $reason = null): PaymentRefundResponse;
}
