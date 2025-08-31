<?php

declare(strict_types=1);

namespace Alazark94\CashierEt;

use Illuminate\Support\ServiceProvider;

class CashierEtServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register any application services.
    }

    public function boot()
    {
        $this->app->singleton('cashier-et', fn ($app) => new CashierEt);
    }
}
