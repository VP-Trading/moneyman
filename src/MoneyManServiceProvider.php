<?php

declare(strict_types=1);

namespace Vptrading\MoneyMan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use Vptrading\MoneyMan\Contracts\WebhookRegistry;
use Vptrading\MoneyMan\Providers\Chapa\WebhookDriver as ChapaWebhookDriver;
use Vptrading\MoneyMan\Providers\SantimPay\WebhookDriver as SantimPayWebhookDriver;
use Vptrading\MoneyMan\Providers\Telebirr\WebhookDriver as TelebirrWebhookDriver;
use Vptrading\MoneyMan\Providers\WebhookRegistry as ProvidersWebhookRegistry;

class MoneyManServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Model::unguard();
        AboutCommand::add('MoneyMan', fn () => [
            'Version' => '1.0.0',
        ]);

        $this->publishes([
            __DIR__.'/../config/moneyman.php' => config_path('moneyman.php'),
        ], 'moneyman-config');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'moneyman-migrations');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/moneyman.php', 'moneyman');
        $this->app->singleton('moneyman', fn ($app) => new MoneyManManager);
        $this->app->singleton(WebhookRegistry::class, function ($app) {
            return new ProvidersWebhookRegistry([
                'chapa' => ChapaWebhookDriver::class,
                'telebirr' => TelebirrWebhookDriver::class,
                'santimpay' => SantimPayWebhookDriver::class,
            ]);
        });
    }
}
