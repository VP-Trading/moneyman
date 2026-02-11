<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\Telebirr;

use InvalidArgumentException;
use Vptrading\MoneyMan\Contracts\WebhookEvent as WebhookEventInterface;

class WebhookEvent implements WebhookEventInterface
{
    public function __construct(private array $payload)
    {
        //
    }

    public function provider(): string
    {
        return 'telebirr';
    }

    public function isSuccess(): bool
    {
        if ($this->payload['trans_currency'] !== 'Completed') {
            return false;
        }

        return true;
    }

    public function getReference(): string
    {
        if (! array_key_exists('merch_order_id', $this->payload)) {
            throw new InvalidArgumentException('No Merch Order Id');
        }

        return $this->payload['merch_order_id'];
    }

    public function getProviderReference(): string
    {
        if (! array_key_exists('payment_order_id', $this->payload)) {
            throw new InvalidArgumentException('No Provider Order Id');
        }

        return $this->payload['payment_order_id'];
    }

    public function getAmount(): float
    {
        return (float) $this->payload['total_amount'];
    }

    public function getCharge(): ?float
    {
        return null;
    }

    public function getCurrency(): string
    {
        return $this->payload['trans_currency'];
    }

    public function getStatus(): string
    {
        return $this->payload['trade_status'];
    }

    public function raw(): array
    {
        return $this->payload;
    }
}
