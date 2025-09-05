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
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);
        $this->app->singleton('moneyman', fn () => new MoneyManManager);
    }
}
