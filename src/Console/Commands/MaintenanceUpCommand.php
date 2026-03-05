<?php

namespace Axvi\Maintenance\Console\Commands;

use Axvi\Maintenance\MaintenanceManager;
use Illuminate\Console\Command;

class MaintenanceUpCommand extends Command
{
    protected $signature = 'maintenance:up';

    protected $description = 'Disable maintenance mode and bring the application back online';

    public function handle(MaintenanceManager $manager): int
    {
        $manager->disable();

        $this->components->info('Application is now live.');

        return self::SUCCESS;
    }
}
