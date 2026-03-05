<?php

namespace Axvi\Maintenance\Tests;

use Axvi\Maintenance\MaintenanceServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            MaintenanceServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Maintenance' => \Axvi\Maintenance\Facades\Maintenance::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
