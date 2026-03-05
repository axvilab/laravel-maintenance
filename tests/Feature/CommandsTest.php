<?php

namespace Axvi\Maintenance\Tests\Feature;

use Axvi\Maintenance\Models\MaintenanceIp;
use Axvi\Maintenance\Models\MaintenanceSetting;
use Axvi\Maintenance\Models\MaintenanceToken;
use Axvi\Maintenance\Tests\TestCase;

class CommandsTest extends TestCase
{
    public function test_maintenance_down_enables_maintenance(): void
    {
        $this->artisan('maintenance:down', ['--message' => 'Deploying'])
            ->assertSuccessful();

        $setting = MaintenanceSetting::current();
        $this->assertTrue($setting->is_active);
        $this->assertSame('Deploying', $setting->message);
    }

    public function test_maintenance_down_with_allow_and_secret(): void
    {
        $this->artisan('maintenance:down', [
            '--allow' => ['10.0.0.1'],
            '--secret' => 'my-secret',
            '--token-name' => 'deploy',
        ])->assertSuccessful();

        $this->assertTrue(MaintenanceIp::isAllowed('10.0.0.1'));
        $this->assertNotNull(MaintenanceToken::findByToken('my-secret'));
    }

    public function test_maintenance_up_disables_maintenance(): void
    {
        $this->artisan('maintenance:down')->assertSuccessful();
        $this->artisan('maintenance:up')->assertSuccessful();

        $this->assertFalse(MaintenanceSetting::current()->is_active);
    }

    public function test_maintenance_status_command(): void
    {
        $this->artisan('maintenance:status')
            ->assertSuccessful();
    }

    public function test_ip_add_and_list(): void
    {
        $this->artisan('maintenance:ip', [
            'action' => 'add',
            'ip' => '192.168.1.1',
            '--label' => 'Office',
        ])->assertSuccessful();

        $this->assertTrue(MaintenanceIp::isAllowed('192.168.1.1'));

        $this->artisan('maintenance:ip', ['action' => 'list'])
            ->assertSuccessful();
    }

    public function test_ip_add_without_ip_fails(): void
    {
        $this->artisan('maintenance:ip', ['action' => 'add'])
            ->assertFailed();
    }

    public function test_ip_remove(): void
    {
        $this->artisan('maintenance:ip', ['action' => 'add', 'ip' => '10.0.0.1']);
        $this->artisan('maintenance:ip', ['action' => 'remove', 'ip' => '10.0.0.1'])
            ->assertSuccessful();

        $this->assertFalse(MaintenanceIp::isAllowed('10.0.0.1'));
    }

    public function test_token_add_and_list(): void
    {
        $this->artisan('maintenance:token', [
            'action' => 'add',
            'name' => 'deploy',
            'token' => 'abc-123',
        ])->assertSuccessful();

        $this->assertNotNull(MaintenanceToken::findByToken('abc-123'));

        $this->artisan('maintenance:token', ['action' => 'list'])
            ->assertSuccessful();
    }

    public function test_token_add_auto_generates_uuid(): void
    {
        $this->artisan('maintenance:token', [
            'action' => 'add',
            'name' => 'auto',
        ])->assertSuccessful();

        $token = MaintenanceToken::where('name', 'auto')->first();
        $this->assertNotNull($token);
        $this->assertNotEmpty($token->token);
    }

    public function test_token_add_without_name_fails(): void
    {
        $this->artisan('maintenance:token', ['action' => 'add'])
            ->assertFailed();
    }

    public function test_token_revoke(): void
    {
        $this->artisan('maintenance:token', ['action' => 'add', 'name' => 'tmp', 'token' => 'x']);
        $this->artisan('maintenance:token', ['action' => 'revoke', 'name' => 'tmp'])
            ->assertSuccessful();

        $this->assertNull(MaintenanceToken::findByToken('x'));
    }

    public function test_invalid_ip_action(): void
    {
        $this->artisan('maintenance:ip', ['action' => 'nope'])
            ->assertFailed();
    }

    public function test_invalid_token_action(): void
    {
        $this->artisan('maintenance:token', ['action' => 'nope'])
            ->assertFailed();
    }
}
