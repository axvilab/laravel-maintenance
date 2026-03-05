<?php

namespace Axvi\Maintenance;

use Axvi\Maintenance\Events\MaintenanceModeDisabled;
use Axvi\Maintenance\Events\MaintenanceModeEnabled;
use Axvi\Maintenance\Models\MaintenanceIp;
use Axvi\Maintenance\Models\MaintenanceSetting;
use Axvi\Maintenance\Models\MaintenanceToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class MaintenanceManager
{
    protected const CACHE_KEY_SETTING = 'maintenance:setting';

    protected const CACHE_KEY_IPS = 'maintenance:ips';

    /**
     * Check if the application is currently in maintenance mode.
     * Auto-disables if ends_at has passed.
     */
    public function isDown(): bool
    {
        $setting = $this->cachedSetting();

        if (! $setting->is_active) {
            return false;
        }

        // Auto-disable when scheduled end time has passed
        if ($setting->ends_at && Carbon::now()->greaterThanOrEqualTo($setting->ends_at)) {
            $this->disable();

            return false;
        }

        return true;
    }

    /**
     * Enable maintenance mode.
     *
     * @param  array{
     *     message?: string,
     *     retry_after?: int,
     *     refresh?: int|null,
     *     ends_at?: string|Carbon|null,
     * }  $options
     */
    public function enable(array $options = []): void
    {
        $endsAt = isset($options['ends_at'])
            ? Carbon::parse($options['ends_at'])
            : null;

        MaintenanceSetting::updateOrCreate([], [
            'is_active' => true,
            'message' => $options['message'] ?? null,
            'retry_after' => $options['retry_after'] ?? 60,
            'refresh' => $options['refresh'] ?? null,
            'ends_at' => $endsAt,
        ]);

        $this->flushCache();

        event(new MaintenanceModeEnabled(
            message: $options['message'] ?? '',
            endsAt: $endsAt?->toIso8601String(),
        ));
    }

    /**
     * Disable maintenance mode.
     */
    public function disable(): void
    {
        MaintenanceSetting::updateOrCreate([], [
            'is_active' => false,
            'ends_at' => null,
        ]);

        $this->flushCache();

        event(new MaintenanceModeDisabled);
    }

    /**
     * Add an IP to the whitelist.
     */
    public function addIp(string $ip, ?string $label = null, ?Carbon $expiresAt = null): void
    {
        MaintenanceIp::updateOrCreate(
            ['ip' => $ip],
            ['label' => $label, 'expires_at' => $expiresAt]
        );

        $this->flushCache();
    }

    /**
     * Remove an IP from the whitelist.
     */
    public function removeIp(string $ip): bool
    {
        $deleted = (bool) MaintenanceIp::where('ip', $ip)->delete();

        if ($deleted) {
            $this->flushCache();
        }

        return $deleted;
    }

    /**
     * Add or update a named bypass token.
     */
    public function addToken(string $name, string $token, ?Carbon $expiresAt = null): void
    {
        MaintenanceToken::updateOrCreate(
            ['name' => $name],
            ['token' => $token, 'expires_at' => $expiresAt]
        );
    }

    /**
     * Revoke a named bypass token.
     */
    public function revokeToken(string $name): bool
    {
        return (bool) MaintenanceToken::where('name', $name)->delete();
    }

    /**
     * Get the current maintenance status as an array (for CLI display).
     */
    public function getStatus(): array
    {
        $setting = MaintenanceSetting::current();
        $ips = MaintenanceIp::active()->get(['ip', 'label', 'expires_at']);
        $tokens = MaintenanceToken::active()->get(['name', 'token', 'expires_at', 'last_used_at']);

        return [
            'is_active' => $setting->is_active,
            'message' => $setting->message,
            'retry_after' => $setting->retry_after,
            'refresh' => $setting->refresh,
            'ends_at' => $setting->ends_at?->toDateTimeString(),
            'ips' => $ips->toArray(),
            'tokens' => $tokens->toArray(),
        ];
    }

    /**
     * Flush all maintenance cache keys.
     */
    public function flushCache(): void
    {
        if (! config('maintenance.cache.enabled', true)) {
            return;
        }

        $store = $this->cacheStore();
        $store->forget(self::CACHE_KEY_SETTING);
        $store->forget(self::CACHE_KEY_IPS);
    }

    /**
     * Get cached setting or fetch from DB.
     */
    protected function cachedSetting(): MaintenanceSetting
    {
        if (! config('maintenance.cache.enabled', true)) {
            return MaintenanceSetting::current();
        }

        $ttl = config('maintenance.cache.ttl', 60);

        /** @var MaintenanceSetting */
        return $this->cacheStore()->remember(self::CACHE_KEY_SETTING, $ttl, function () {
            return MaintenanceSetting::current();
        });
    }

    /**
     * Get cached list of allowed IPs.
     */
    public function isIpAllowed(string $ip): bool
    {
        if (! config('maintenance.cache.enabled', true)) {
            return MaintenanceIp::isAllowed($ip);
        }

        $ttl = config('maintenance.cache.ttl', 60);

        /** @var string[] $allowedIps */
        $allowedIps = $this->cacheStore()->remember(self::CACHE_KEY_IPS, $ttl, function () {
            return MaintenanceIp::active()->pluck('ip')->all();
        });

        return in_array($ip, $allowedIps, true);
    }

    protected function cacheStore(): \Illuminate\Contracts\Cache\Repository
    {
        $store = config('maintenance.cache.store');

        return Cache::store($store);
    }
}
