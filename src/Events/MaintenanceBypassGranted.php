<?php

namespace Axvi\Maintenance\Events;

use Illuminate\Foundation\Events\Dispatchable;

class MaintenanceBypassGranted
{
    use Dispatchable;

    public function __construct(
        public readonly string $type, // 'ip' | 'cookie' | 'header' | 'url'
        public readonly string $value, // the IP or token used
    ) {}
}
