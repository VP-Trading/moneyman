<?php

declare(strict_types=1);

namespace Alazark94\MoneyMan\Providers;

use Alazark94\MoneyMan\Contracts\PaymentProvider;
use Alazark94\MoneyMan\Contracts\Responses\PaymentInitiateResponse;
use Alazark94\MoneyMan\Contracts\Responses\PaymentRefundResponse;
use Alazark94\MoneyMan\Contracts\Responses\PaymentVerifyResponse;
use Alazark94\MoneyMan\ValueObjects\User;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;

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
