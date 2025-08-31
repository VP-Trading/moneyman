<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Attributes\WithConfig;
use Orchestra\Testbench\Attributes\WithEnv;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

// #[WithEnv('DB_CONNECTION', 'testing')]
// #[WithConfig('database.default', 'testing')]
// #[WithMigration('chapa_webhook_events')]
abstract class TestCase extends OrchestraTestCase
{
    // use RefreshDatabase;
    protected function getPackageProviders($app)
    {
        return [
            // \Vptrading\ChapaLaravel\ChapaServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        tap($app['config'], function (Repository $config): void {
            $config->set('database.default', 'testbench');
            $config->set('database.connections.testbench', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);
        });
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/../database/migrations'
        );
    }
}
