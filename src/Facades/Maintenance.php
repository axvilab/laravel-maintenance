<?php

namespace Axvi\Maintenance\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isDown()
 * @method static void enable(array $options = [])
 * @method static void disable()
 * @method static void addIp(string $ip, ?string $label = null, ?\Illuminate\Support\Carbon $expiresAt = null)
 * @method static bool removeIp(string $ip)
 * @method static bool isIpAllowed(string $ip)
 * @method static void addToken(string $name, string $token, ?\Illuminate\Support\Carbon $expiresAt = null)
 * @method static bool revokeToken(string $name)
 * @method static array getStatus()
 * @method static void flushCache()
 *
 * @see \Axvi\Maintenance\MaintenanceManager
 */
class Maintenance extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'maintenance';
    }
}
