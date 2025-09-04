<?php

declare(strict_types=1);

namespace Alazark94\CashierEt;

use Illuminate\Support\ServiceProvider;

class CashierEtServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/cashier-et.php', 'cashier-et');
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        $this->publishesMigrations([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ]);
        $this->app->singleton('cashier-et', fn($app) => new CashierEtManager());
    }
}
