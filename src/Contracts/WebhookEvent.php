<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Contracts;

interface WebhookEvent
{
    public function provider(): string;

    public function isSuccess(): bool;

    public function getReference(): string;

    public function getProviderReference(): string;

    public function getAmount(): float;

    public function getCharge(): ?float;

    public function getCurrency(): string;

    public function getStatus(): string;

    public function raw(): array;
}
