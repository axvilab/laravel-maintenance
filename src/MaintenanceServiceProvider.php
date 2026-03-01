<?php

namespace Axvi\Maintenance;

use Illuminate\Support\ServiceProvider;

class MaintenanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/maintenance.php',
            'maintenance'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/maintenance.php' => config_path('maintenance.php'),
        ], 'maintenance-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'maintenance-migrations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/maintenance'),
        ], 'maintenance-views');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'maintenance');
    }
}
