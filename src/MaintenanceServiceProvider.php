<?php

namespace Axvi\Maintenance;

use Axvi\Maintenance\Console\Commands\MaintenanceDownCommand;
use Axvi\Maintenance\Console\Commands\MaintenanceIpCommand;
use Axvi\Maintenance\Console\Commands\MaintenanceStatusCommand;
use Axvi\Maintenance\Console\Commands\MaintenanceTokenCommand;
use Axvi\Maintenance\Console\Commands\MaintenanceUpCommand;
use Axvi\Maintenance\Http\Middleware\CheckMaintenanceMode;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Support\ServiceProvider;

class MaintenanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/maintenance.php',
            'maintenance'
        );

        // Register MaintenanceManager as a singleton
        $this->app->singleton('maintenance', fn () => new MaintenanceManager);
        $this->app->alias('maintenance', MaintenanceManager::class);
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/maintenance.php' => config_path('maintenance.php'),
        ], 'maintenance-config');

        // Load migrations automatically and allow publishing
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'maintenance-migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/maintenance'),
        ], 'maintenance-views');

        // Load package views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'maintenance');

        // Register Artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                MaintenanceDownCommand::class,
                MaintenanceUpCommand::class,
                MaintenanceStatusCommand::class,
                MaintenanceIpCommand::class,
                MaintenanceTokenCommand::class,
            ]);
        }

        // Replace Laravel's built-in maintenance middleware with ours
        $this->app->booted(function () {
            $kernel = $this->app->make(Kernel::class);

            // Laravel 10 / 11 with Http\Kernel
            if ($kernel instanceof \Illuminate\Foundation\Http\Kernel) {
                if (method_exists($kernel, 'removeMiddleware')) {
                    $kernel->removeMiddleware(PreventRequestsDuringMaintenance::class);
                }

                $kernel->prependMiddleware(CheckMaintenanceMode::class);
            }
        });

        // Register token bypass route (e.g. GET /maintenance/{token})
        $this->registerBypassRoute();
    }

    protected function registerBypassRoute(): void
    {
        if (! config('maintenance.bypass_route.enabled', true)) {
            return;
        }

        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app['router'];

        $prefix = config('maintenance.bypass_route.prefix', 'maintenance');

        $router->get("{$prefix}/{maintenanceToken}", function (string $maintenanceToken) {
            /** @var CheckMaintenanceMode $middleware */
            $middleware = $this->app->make(CheckMaintenanceMode::class);

            return $middleware->handleBypassRoute(request(), $maintenanceToken);
        })->name('maintenance.bypass');
    }
}
