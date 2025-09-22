<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\Telebirr\Dtos;

use Vptrading\MoneyMan\Contracts\Responses\PaymentVerifyResponse as PaymentVerifyResponseContract;

class PaymentVerifyResponse implements PaymentVerifyResponseContract
{
    public function __construct(
        public string $status,
        public string $message,
        public ?array $data,
        public ?string $transactionId
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
}
