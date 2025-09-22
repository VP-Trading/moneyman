<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan\Enums;

enum Provider: string
{
    case Telebirr = 'telebirr';

    case Chapa = 'chapa';

    case SantimPay = 'santimpay';
}
