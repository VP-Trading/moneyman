<?php

declare(strict_types=1);

namespace Alazark94\MoneyMan\Providers\SantimPay\Dtos;

use Alazark94\MoneyMan\Contracts\Responses\PaymentVerifyResponse as ResponsesPaymentVerifyResponse;

class PaymentVerifyResponse implements ResponsesPaymentVerifyResponse
{
    public function __construct(
        public string $status,
        public ?string $message,
        public ?array $data,
        public ?string $transactionId = null
    ) {}

    public function isSuccessful(): bool
    {
        return $this->status === 'COMPLETED';
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
