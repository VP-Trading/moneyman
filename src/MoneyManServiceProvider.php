<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan;

use Illuminate\Support\ServiceProvider;

class MoneyManServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/moneyman.php', 'moneyman');
        $this->app->singleton('moneyman', fn ($app) => new MoneyManManager);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/moneyman.php' => config_path('moneyman.php'),
        ]);
    }
}
