<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\SantimPay;

use InvalidArgumentException;
use Vptrading\MoneyMan\Contracts\WebhookEvent as WebhookEventInterface;

class WebhookEvent implements WebhookEventInterface
{
    public function __construct(private array $payload)
    {
        // throw new \Exception('Not implemented');
    }

    public function provider(): string
    {
        return 'santimpay';
    }

    public function isSuccess(): bool
    {
        if ($this->payload['status'] !== 'COMPLETED') {
            return false;
        }

        return true;
    }

    public function getReference(): string
    {
        if (! array_key_exists('refId', $this->payload)) {
            throw new InvalidArgumentException('No Merch Order Id');
        }

        return $this->payload['refId'];
    }

    public function getProviderReference(): string
    {
        if (! array_key_exists('txnId', $this->payload)) {
            throw new InvalidArgumentException('No Provider Order Id');
        }

        return $this->payload['txnId'];
    }

    public function getAmount(): float
    {
        return (float) $this->payload['amount'];
    }

    public function getCharge(): ?float
    {
        return null;
    }

    public function getCurrency(): string
    {
        return $this->payload['currency'];
    }

    public function getStatus(): string
    {
        return $this->payload['status'];
    }

    public function raw(): array
    {
        return $this->payload;
    }
}
