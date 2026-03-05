<?php

namespace Axvi\Maintenance\Console\Commands;

use Axvi\Maintenance\MaintenanceManager;
use Axvi\Maintenance\Models\MaintenanceToken;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class MaintenanceTokenCommand extends Command
{
    protected $signature = 'maintenance:token
        {action      : Action to perform: add, revoke, list}
        {name?       : Token name (used with add / revoke)}
        {token?      : Token value (used with add; auto-generated if omitted)}
        {--expires-at= : Expiry datetime in ISO8601 format (used with add)}
    ';

    protected $description = 'Manage maintenance bypass tokens';

    public function handle(MaintenanceManager $manager): int
    {
        return match ($this->argument('action')) {
            'add' => $this->add($manager),
            'revoke' => $this->revoke($manager),
            'list' => $this->list(),
            default => $this->invalidAction(),
        };
    }

    protected function add(MaintenanceManager $manager): int
    {
        $name = $this->argument('name');
        if (! $name) {
            $this->components->error('Please provide a token name.');

            return self::FAILURE;
        }

        $token = $this->argument('token') ?: Str::uuid()->toString();
        $expiresAt = $this->option('expires-at') ? Carbon::parse($this->option('expires-at')) : null;

        $manager->addToken($name, $token, $expiresAt);

        $this->components->info("Token <fg=green>{$name}</> added.");
        $this->line("  Token value: <fg=yellow>{$token}</>");

        return self::SUCCESS;
    }

    protected function revoke(MaintenanceManager $manager): int
    {
        $name = $this->argument('name');
        if (! $name) {
            $this->components->error('Please provide a token name.');

            return self::FAILURE;
        }

        $revoked = $manager->revokeToken($name);

        if ($revoked) {
            $this->components->info("Token <fg=yellow>{$name}</> revoked.");
        } else {
            $this->components->warn("Token {$name} was not found.");
        }

        return self::SUCCESS;
    }

    protected function list(): int
    {
        $tokens = MaintenanceToken::active()->get(['name', 'token', 'expires_at', 'last_used_at']);

        if ($tokens->isEmpty()) {
            $this->components->info('No active tokens.');

            return self::SUCCESS;
        }

        $this->table(
            ['Name', 'Token', 'Expires At', 'Last Used'],
            $tokens->map(fn ($t) => [
                $t->name,
                $t->token,
                $t->expires_at?->toDateTimeString() ?? '—',
                $t->last_used_at?->toDateTimeString() ?? '—',
            ])->toArray()
        );

        return self::SUCCESS;
    }

    protected function invalidAction(): int
    {
        $this->components->error('Invalid action. Use: add, revoke, list');

        return self::FAILURE;
    }
}
