<?php

namespace Axvi\Maintenance\Http\Controllers;

use Axvi\Maintenance\Http\Middleware\CheckMaintenanceMode;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the maintenance bypass secret URL: GET /{prefix}/{token}.
 *
 * Implemented as an invokable controller rather than a route Closure so the
 * application's routes remain cacheable. A Closure defined inside the service
 * provider is bound to $this, which would make `route:cache` serialize the
 * whole container and exhaust memory.
 */
class BypassController
{
    public function __construct(protected CheckMaintenanceMode $middleware) {}

    public function __invoke(Request $request, string $maintenanceToken): Response
    {
        return $this->middleware->handleBypassRoute($request, $maintenanceToken);
    }
}
