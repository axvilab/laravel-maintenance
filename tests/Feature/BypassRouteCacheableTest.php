<?php

namespace Axvi\Maintenance\Tests\Feature;

use Axvi\Maintenance\Http\Controllers\BypassController;
use Axvi\Maintenance\Tests\TestCase;
use Closure;
use Illuminate\Support\Facades\Route;

class BypassRouteCacheableTest extends TestCase
{
    public function test_bypass_route_uses_a_controller_not_a_closure(): void
    {
        $route = Route::getRoutes()->getByName('maintenance.bypass');

        $this->assertNotNull($route, 'The maintenance.bypass route should be registered.');
        $this->assertFalse(
            $route->getAction('uses') instanceof Closure,
            'The bypass route must not use a Closure, otherwise route:cache serializes the whole container and exhausts memory.'
        );
        $this->assertSame(BypassController::class.'@__invoke', $route->getAction('uses'));
    }

    public function test_bypass_route_can_be_prepared_for_serialization(): void
    {
        $route = Route::getRoutes()->getByName('maintenance.bypass');

        $route->prepareForSerialization();

        $this->assertTrue(true);
    }
}
