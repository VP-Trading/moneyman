<?php

declare(strict_types=1);

namespace Alazark94\MoneyMan\Providers\Telebirr\Dtos;

use Alazark94\MoneyMan\Contracts\Responses\PaymentInitiateResponse as PaymentInitiateResponseContract;

class PaymentInitiateResponse implements PaymentInitiateResponseContract
{
    public function __construct(
        public string $status,
        public ?string $message,
        public ?string $transactionId = null,
        public ?string $checkoutUrl = null,
    ) {
        //
    }

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
