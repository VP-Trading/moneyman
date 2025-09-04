<?php

declare(strict_types=1);

namespace Alazark94\CashierEt\Facades;

use Illuminate\Support\Facades\Facade;

class CashierEt extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cashier-et';
    }
}
