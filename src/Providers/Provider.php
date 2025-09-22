<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers;

use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Vptrading\MoneyMan\Contracts\PaymentProvider;
use Vptrading\MoneyMan\Contracts\Responses\PaymentInitiateResponse;
use Vptrading\MoneyMan\Contracts\Responses\PaymentRefundResponse;
use Vptrading\MoneyMan\Contracts\Responses\PaymentVerifyResponse;
use Vptrading\MoneyMan\ValueObjects\User;

abstract class Provider implements PaymentProvider
{
    protected ISOCurrencies $currencies;

    protected DecimalMoneyFormatter $formatter;

    public function __construct()
    {
        $this->currencies = new ISOCurrencies;
        $this->formatter = new DecimalMoneyFormatter($this->currencies);
    }

    /**
     * @throws \InvalidArgumentException
     */
    abstract public function initiate(Money $money, User $user, string $returnUrl, ?string $reason = null, ?array $parameters = []): PaymentInitiateResponse;

    abstract public function verify(string $transactionId): PaymentVerifyResponse;

    /**
     * @throws \LogicException
     */
    abstract public function refund(string $transactionId, ?Money $amount = null, ?string $reason = null): PaymentRefundResponse;
}
