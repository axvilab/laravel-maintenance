<?php

namespace Axvi\Maintenance\Console\Commands;

use Axvi\Maintenance\MaintenanceManager;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class MaintenanceDownCommand extends Command
{
    protected $signature = 'maintenance:down
        {--message=           : Custom message to show on the 503 page}
        {--retry=60           : Retry-After header value in seconds}
        {--refresh=           : Refresh header value in seconds}
        {--allow=*            : IP address(es) to whitelist (can repeat)}
        {--secret=            : Bypass token value}
        {--token-name=bypass  : Name for the bypass token}
        {--ends-at=           : ISO8601 datetime when maintenance ends automatically}
    ';

    protected $description = 'Enable maintenance mode with optional IP whitelist and bypass token';

    public function handle(MaintenanceManager $manager): int
    {
        $manager->enable([
            'message' => $this->option('message'),
            'retry_after' => (int) $this->option('retry'),
            'refresh' => $this->option('refresh') ? (int) $this->option('refresh') : null,
            'ends_at' => $this->option('ends-at') ? Carbon::parse($this->option('ends-at')) : null,
        ]);

        // Add whitelisted IPs
        foreach ((array) $this->option('allow') as $ip) {
            if ($ip) {
                $manager->addIp($ip);
                $this->line("  ✓ IP <info>{$ip}</info> whitelisted");
            }
        }

        // Add bypass token
        if ($secret = $this->option('secret')) {
            $name = $this->option('token-name') ?: 'bypass';
            $manager->addToken($name, $secret);
            $this->line("  ✓ Bypass token <info>{$name}</info> set");
        }

        $this->components->info('Application is now in maintenance mode.');

        return self::SUCCESS;
    }
}
