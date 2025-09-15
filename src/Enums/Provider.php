<?php

declare(strict_types=1);

namespace Alazark94\MoneyMan\Enums;

enum Provider: string
{
    case Telebirr = 'telebirr';

    case Chapa = 'chapa';

    case SantimPay = 'santimpay';
}
