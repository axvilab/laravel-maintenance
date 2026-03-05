<?php

namespace Axvi\Maintenance\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class MaintenanceToken extends Model
{
    public function getTable(): string
    {
        return config('maintenance.tables.tokens', 'maintenance_tokens');
    }

    public function getConnectionName(): ?string
    {
        return config('maintenance.connection') ?? parent::getConnectionName();
    }

    protected $fillable = [
        'name',
        'token',
        'expires_at',
        'last_used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * Scope: only non-expired tokens.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', Carbon::now());
        });
    }

    /**
     * Find an active token by its value.
     */
    public static function findByToken(string $token): ?static
    {
        return static::active()->where('token', $token)->first();
    }

    /**
     * Mark this token as used right now.
     */
    public function markUsed(): void
    {
        $this->update(['last_used_at' => Carbon::now()]);
    }
}
