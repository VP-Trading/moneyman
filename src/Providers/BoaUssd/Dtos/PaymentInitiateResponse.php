<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\BoaUssd\Dtos;

use Override;
use Vptrading\MoneyMan\Contracts\Responses\PaymentInitiateResponse as PaymentInitiateResponseContract;

class PaymentInitiateResponse implements PaymentInitiateResponseContract
{
    public function __construct(
        public string $status,
        public ?string $message,
        public ?string $transactionId = null,
        public ?string $checkoutUrl = null,
        public array $validationErrors = []
    ) {}

    #[Override]
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    #[Override]
    public function getCheckoutUrl(): ?string
    {
        return null;
    }

    #[Override]
    public function getMessage(): ?string
    {
        return $this->message;
    }

    #[Override]
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }
}
