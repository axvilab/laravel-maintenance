<?php

namespace Axvi\Maintenance\Tests\Feature;

use Axvi\Maintenance\MaintenanceManager;
use Axvi\Maintenance\Models\MaintenanceToken;
use Axvi\Maintenance\Tests\TestCase;

class MiddlewareTest extends TestCase
{
    private MaintenanceManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = app(MaintenanceManager::class);

        // Register a simple test route
        $this->app['router']->get('/test', fn () => 'OK')
            ->middleware(\Axvi\Maintenance\Http\Middleware\CheckMaintenanceMode::class);
    }

    public function test_passes_through_when_not_in_maintenance(): void
    {
        $this->get('/test')->assertOk()->assertSee('OK');
    }

    public function test_returns_503_when_in_maintenance(): void
    {
        $this->manager->enable(['message' => 'Down for updates']);

        $this->get('/test')->assertStatus(503);
    }

    public function test_returns_json_503_for_api_requests(): void
    {
        $this->manager->enable(['message' => 'API down']);

        $this->getJson('/test')
            ->assertStatus(503)
            ->assertJson(['message' => 'API down']);
    }

    public function test_whitelisted_ip_bypasses_maintenance(): void
    {
        $this->manager->enable();
        $this->manager->addIp('127.0.0.1');

        $this->get('/test')->assertOk();
    }

    public function test_header_token_bypasses_maintenance(): void
    {
        $this->manager->enable();
        $this->manager->addToken('api', 'my-secret');

        $this->get('/test', ['X-Maintenance-Token' => 'my-secret'])
            ->assertOk();

        // Verify token was marked as used
        $token = MaintenanceToken::where('name', 'api')->first();
        $this->assertNotNull($token->last_used_at);
    }

    public function test_invalid_header_token_gets_503(): void
    {
        $this->manager->enable();

        $this->get('/test', ['X-Maintenance-Token' => 'wrong-token'])
            ->assertStatus(503);
    }

    public function test_cookie_token_bypasses_maintenance(): void
    {
        $this->manager->enable();
        $this->manager->addToken('web', 'cookie-secret');

        $cookieName = config('maintenance.middleware.cookie_name', 'laravel_maintenance');

        $this->call('GET', '/test', [], [$cookieName => 'cookie-secret'])
            ->assertOk();
    }

    public function test_bypass_route_sets_cookie_and_redirects(): void
    {
        $this->manager->enable();
        $this->manager->addToken('bypass', 'my-bypass-token');

        $prefix = config('maintenance.bypass_route.prefix', 'maintenance');

        $response = $this->get("/{$prefix}/my-bypass-token");

        $response->assertRedirect('/');
        $response->assertCookie(config('maintenance.middleware.cookie_name', 'laravel_maintenance'));
    }

    public function test_bypass_route_returns_404_for_invalid_token(): void
    {
        $this->manager->enable();

        $prefix = config('maintenance.bypass_route.prefix', 'maintenance');

        $this->get("/{$prefix}/invalid-token")->assertNotFound();
    }

    public function test_retry_after_header_is_present(): void
    {
        $this->manager->enable(['retry_after' => 120]);

        $response = $this->get('/test');

        $response->assertStatus(503);
        $response->assertHeader('Retry-After', 120);
    }

    public function test_excluded_path_bypasses_maintenance(): void
    {
        config(['maintenance.except' => ['api/health', 'webhook/*']]);

        $this->app['router']->get('/api/health', fn () => 'healthy')
            ->middleware(\Axvi\Maintenance\Http\Middleware\CheckMaintenanceMode::class);
        $this->app['router']->get('/webhook/stripe', fn () => 'ok')
            ->middleware(\Axvi\Maintenance\Http\Middleware\CheckMaintenanceMode::class);

        $this->manager->enable();

        $this->get('/api/health')->assertOk()->assertSee('healthy');
        $this->get('/webhook/stripe')->assertOk()->assertSee('ok');
        $this->get('/test')->assertStatus(503);
    }

    public function test_cached_ip_check_works(): void
    {
        config(['maintenance.cache.enabled' => true, 'maintenance.cache.ttl' => 60]);

        $this->manager->enable();
        $this->manager->addIp('127.0.0.1');

        // First request — caches the IP list
        $this->get('/test')->assertOk();

        // Second request — should use cache
        $this->get('/test')->assertOk();
    }
}
