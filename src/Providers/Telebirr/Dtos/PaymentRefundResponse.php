<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\Telebirr\Dtos;

use Vptrading\MoneyMan\Contracts\Responses\PaymentRefundResponse as PaymentRefundResponseContract;

class PaymentRefundResponse implements PaymentRefundResponseContract
{
    public function __construct(
        public string $status,
        public ?string $message = null,
        public ?array $data = [],
        public ?string $transactionId = null
    ) {}

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }
}
