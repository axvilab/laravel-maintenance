<?php

namespace Axvi\Maintenance\Console\Commands;

use Axvi\Maintenance\MaintenanceManager;
use Axvi\Maintenance\Models\MaintenanceIp;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class MaintenanceIpCommand extends Command
{
    protected $signature = 'maintenance:ip
        {action : Action to perform: add, remove, list}
        {ip?    : The IP address}
        {--label= : Label for the IP (used with add)}
        {--expires-at= : Expiry datetime in ISO8601 format (used with add)}
    ';

    protected $description = 'Manage whitelisted IP addresses';

    public function handle(MaintenanceManager $manager): int
    {
        return match ($this->argument('action')) {
            'add' => $this->add($manager),
            'remove' => $this->remove($manager),
            'list' => $this->list(),
            default => $this->invalidAction(),
        };
    }

    protected function add(MaintenanceManager $manager): int
    {
        $ip = $this->argument('ip');
        if (! $ip) {
            $this->components->error('Please provide an IP address.');

            return self::FAILURE;
        }

        $expiresAt = $this->option('expires-at') ? Carbon::parse($this->option('expires-at')) : null;
        $manager->addIp($ip, $this->option('label'), $expiresAt);

        $this->components->info("IP <fg=green>{$ip}</> added to whitelist.");

        return self::SUCCESS;
    }

    protected function remove(MaintenanceManager $manager): int
    {
        $ip = $this->argument('ip');
        if (! $ip) {
            $this->components->error('Please provide an IP address.');

            return self::FAILURE;
        }

        $removed = $manager->removeIp($ip);

        if ($removed) {
            $this->components->info("IP <fg=yellow>{$ip}</> removed from whitelist.");
        } else {
            $this->components->warn("IP {$ip} was not found in the whitelist.");
        }

        return self::SUCCESS;
    }

    protected function list(): int
    {
        $ips = MaintenanceIp::active()->get(['ip', 'label', 'expires_at']);

        if ($ips->isEmpty()) {
            $this->components->info('No whitelisted IPs.');

            return self::SUCCESS;
        }

        $this->table(
            ['IP', 'Label', 'Expires At'],
            $ips->map(fn ($ip) => [
                $ip->ip,
                $ip->label ?? '—',
                $ip->expires_at?->toDateTimeString() ?? '—',
            ])->toArray()
        );

        return self::SUCCESS;
    }

    protected function invalidAction(): int
    {
        $this->components->error('Invalid action. Use: add, remove, list');

        return self::FAILURE;
    }
}
