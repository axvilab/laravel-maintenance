<?php

namespace Axvi\Maintenance\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class MaintenanceIp extends Model
{
    public function getTable(): string
    {
        return config('maintenance.tables.ips', 'maintenance_ips');
    }

    public function getConnectionName(): ?string
    {
        return config('maintenance.connection') ?? parent::getConnectionName();
    }

    protected $fillable = [
        'ip',
        'label',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Scope: only non-expired IPs.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', Carbon::now());
        });
    }

    /**
     * Check if a given IP is whitelisted.
     */
    public static function isAllowed(string $ip): bool
    {
        return static::active()->where('ip', $ip)->exists();
    }
}
