<?php

namespace Axvi\Maintenance\Events;

use Illuminate\Foundation\Events\Dispatchable;

class MaintenanceModeEnabled
{
    use Dispatchable;

    public function __construct(
        public readonly string $message = '',
        public readonly ?string $endsAt = null,
    ) {}
}
