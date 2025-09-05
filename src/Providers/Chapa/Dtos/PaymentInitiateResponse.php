<?php

declare(strict_types=1);

namespace Alazark94\MoneyMan\Providers\Chapa\Dtos;

use Alazark94\MoneyMan\Contracts\Responses\PaymentInitiateResponse as PaymentInitiateResponseContract;

readonly class PaymentInitiateResponse implements PaymentInitiateResponseContract
{
    public function __construct(
        public string $status,
        public ?string $message,
        public ?string $transactionId = null,
        public ?string $checkoutUrl = null,
        public array $validationErrors = []
    ) {}

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function hasValidationErrors(): bool
    {
        return ! empty($this->validationErrors);
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function getCheckoutUrl(): ?string
    {
        return $this->checkoutUrl;
    }
}
