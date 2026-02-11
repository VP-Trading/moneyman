<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\Chapa;

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
        return 'chapa';
    }

    public function isSuccess(): bool
    {
        if ($this->payload['event'] !== 'charge.success') {
            return false;
        }

        return true;
    }

    public function getReference(): string
    {
        if (! array_key_exists('tx_ref', $this->payload)) {
            throw new InvalidArgumentException('No Merch Order Id');
        }

        return $this->payload['tx_ref'];
    }

    public function getProviderReference(): string
    {
        if (! array_key_exists('reference', $this->payload)) {
            throw new InvalidArgumentException('No Provider Order Id');
        }

        return $this->payload['reference'];
    }

    public function getAmount(): float
    {
        return (float) $this->payload['amount'];
    }

    public function getCharge(): ?float
    {
        return (float) $this->payload['charge'];
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
