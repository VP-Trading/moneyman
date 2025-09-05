<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Config\Repository;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Alazark94\MoneyMan\MoneyManServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        // tap($app['config'], function (Repository $config): void {
        //     $config->set('database.default', 'testbench');
        //     $config->set('database.connections.testbench', [
        //         'driver' => 'sqlite',
        //         'database' => ':memory:',
        //         'prefix' => '',
        //     ]);
        // });
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            __DIR__.'/../database/migrations'
        );
    }
}
