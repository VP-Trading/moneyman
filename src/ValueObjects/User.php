<?php

declare(strict_types=1);

namespace Alazark94\MoneyMan\ValueObjects;

readonly class User
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $phoneNumber
    ) {}
}
