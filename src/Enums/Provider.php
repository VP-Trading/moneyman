<?php

declare(strict_types=1);

namespace Alazark94\CashierEt\Enums;

enum Provider: string
{
    case TelebirrUssd = 'telebirr_ussd';

    case TelebirrH5 = 'telebirr_h5';

    case Chapa = 'chapa';

    case SafaricomUssd = 'safaricom_ussd';
}
