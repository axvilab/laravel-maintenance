<?php

namespace Axvi\Maintenance\Events;

use Illuminate\Foundation\Events\Dispatchable;

class MaintenanceModeDisabled
{
    use Dispatchable;

    public function __construct() {}
}
