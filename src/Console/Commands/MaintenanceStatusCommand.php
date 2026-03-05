<?php

namespace Axvi\Maintenance\Console\Commands;

use Axvi\Maintenance\MaintenanceManager;
use Illuminate\Console\Command;

class MaintenanceStatusCommand extends Command
{
    protected $signature = 'maintenance:status';

    protected $description = 'Show the current maintenance mode status';

    public function handle(MaintenanceManager $manager): int
    {
        $status = $manager->getStatus();

        $this->components->info('Maintenance Mode Status');

        $this->table(['Setting', 'Value'], [
            ['Active', $status['is_active'] ? '<fg=red>YES</>' : '<fg=green>NO</>'],
            ['Message', $status['message'] ?? '—'],
            ['Retry-After', $status['retry_after'].'s'],
            ['Refresh', $status['refresh'] ? $status['refresh'].'s' : '—'],
            ['Ends At', $status['ends_at'] ?? '—'],
        ]);

        if (! empty($status['ips'])) {
            $this->newLine();
            $this->line('<fg=cyan>Whitelisted IPs:</>');
            $this->table(
                ['IP', 'Label', 'Expires At'],
                array_map(fn ($ip) => [
                    $ip['ip'],
                    $ip['label'] ?? '—',
                    $ip['expires_at'] ?? '—',
                ], $status['ips'])
            );
        }

        if (! empty($status['tokens'])) {
            $this->newLine();
            $this->line('<fg=cyan>Bypass Tokens:</>');
            $this->table(
                ['Name', 'Token', 'Expires At', 'Last Used'],
                array_map(fn ($t) => [
                    $t['name'],
                    $t['token'],
                    $t['expires_at'] ?? '—',
                    $t['last_used_at'] ?? '—',
                ], $status['tokens'])
            );
        }

        return self::SUCCESS;
    }
}
