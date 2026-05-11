<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Providers\BoaUssd\Dtos;

use Override;
use Vptrading\MoneyMan\Contracts\Responses\PaymentVerifyResponse as PaymentVerifyResponseContract;

class PaymentVerifyResponse implements PaymentVerifyResponseContract
{
    public function __construct(
        public string $status,
        public ?string $message,
        public ?array $data,
        public ?string $transactionId
    ) {}

    #[Override]
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
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
