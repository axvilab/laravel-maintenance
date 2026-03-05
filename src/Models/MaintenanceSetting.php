<?php

namespace Axvi\Maintenance\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceSetting extends Model
{
    public function getTable(): string
    {
        return config('maintenance.tables.settings', 'maintenance_settings');
    }

    public function getConnectionName(): ?string
    {
        return config('maintenance.connection') ?? parent::getConnectionName();
    }

    protected $fillable = [
        'is_active',
        'message',
        'retry_after',
        'refresh',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'retry_after' => 'integer',
        'refresh' => 'integer',
        'ends_at' => 'datetime',
    ];

    /**
     * Get or create the single settings row.
     */
    public static function current(): static
    {
        return static::firstOrCreate([], [
            'is_active' => false,
            'retry_after' => 60,
        ]);
    }
}
