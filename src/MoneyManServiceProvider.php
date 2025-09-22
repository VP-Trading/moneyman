<?php

declare(strict_types=1);

namespace Alazark94\MoneyMan;

use Illuminate\Support\ServiceProvider;

class MoneyManServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/moneyman.php', 'moneyman');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/moneyman.php' => config_path('moneyman.php'),
        ]);

        $this->app->singleton('moneyman', fn () => new MoneyManManager);
    }
}
