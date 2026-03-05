<?php

namespace Axvi\Maintenance\Tests\Unit;

use Axvi\Maintenance\Events\MaintenanceModeDisabled;
use Axvi\Maintenance\Events\MaintenanceModeEnabled;
use Axvi\Maintenance\MaintenanceManager;
use Axvi\Maintenance\Models\MaintenanceIp;
use Axvi\Maintenance\Models\MaintenanceSetting;
use Axvi\Maintenance\Models\MaintenanceToken;
use Axvi\Maintenance\Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

class MaintenanceManagerTest extends TestCase
{
    private MaintenanceManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = app(MaintenanceManager::class);
    }

    public function test_is_down_returns_false_by_default(): void
    {
        $this->assertFalse($this->manager->isDown());
    }

    public function test_enable_sets_maintenance_mode(): void
    {
        Event::fake();

        $this->manager->enable(['message' => 'Updating...']);

        $this->assertTrue($this->manager->isDown());
        $this->assertSame('Updating...', MaintenanceSetting::current()->message);

        Event::assertDispatched(MaintenanceModeEnabled::class, function ($event) {
            return $event->message === 'Updating...';
        });
    }

    public function test_disable_turns_off_maintenance_mode(): void
    {
        Event::fake();

        $this->manager->enable();
        $this->manager->disable();

        $this->assertFalse($this->manager->isDown());
        Event::assertDispatched(MaintenanceModeDisabled::class);
    }

    public function test_auto_disables_when_ends_at_has_passed(): void
    {
        $this->manager->enable([
            'ends_at' => Carbon::now()->subMinute(),
        ]);

        $this->assertFalse($this->manager->isDown());
        $this->assertFalse(MaintenanceSetting::current()->is_active);
    }

    public function test_stays_active_when_ends_at_is_in_future(): void
    {
        $this->manager->enable([
            'ends_at' => Carbon::now()->addHour(),
        ]);

        $this->assertTrue($this->manager->isDown());
    }

    public function test_add_and_remove_ip(): void
    {
        $this->manager->addIp('192.168.1.1', 'Office');

        $this->assertTrue(MaintenanceIp::isAllowed('192.168.1.1'));
        $this->assertFalse(MaintenanceIp::isAllowed('10.0.0.1'));

        $this->assertTrue($this->manager->removeIp('192.168.1.1'));
        $this->assertFalse(MaintenanceIp::isAllowed('192.168.1.1'));
    }

    public function test_remove_nonexistent_ip_returns_false(): void
    {
        $this->assertFalse($this->manager->removeIp('1.2.3.4'));
    }

    public function test_expired_ip_is_not_allowed(): void
    {
        $this->manager->addIp('192.168.1.1', 'Temp', Carbon::now()->subHour());

        $this->assertFalse(MaintenanceIp::isAllowed('192.168.1.1'));
    }

    public function test_add_and_revoke_token(): void
    {
        $this->manager->addToken('deploy', 'secret-123');

        $token = MaintenanceToken::findByToken('secret-123');
        $this->assertNotNull($token);
        $this->assertSame('deploy', $token->name);

        $this->assertTrue($this->manager->revokeToken('deploy'));
        $this->assertNull(MaintenanceToken::findByToken('secret-123'));
    }

    public function test_revoke_nonexistent_token_returns_false(): void
    {
        $this->assertFalse($this->manager->revokeToken('nope'));
    }

    public function test_expired_token_is_not_found(): void
    {
        $this->manager->addToken('old', 'old-token', Carbon::now()->subHour());

        $this->assertNull(MaintenanceToken::findByToken('old-token'));
    }

    public function test_token_mark_used_updates_timestamp(): void
    {
        $this->manager->addToken('test', 'tok-123');

        $token = MaintenanceToken::findByToken('tok-123');
        $this->assertNull($token->last_used_at);

        $token->markUsed();
        $token->refresh();

        $this->assertNotNull($token->last_used_at);
    }

    public function test_get_status_returns_complete_array(): void
    {
        $this->manager->enable(['message' => 'Down']);
        $this->manager->addIp('10.0.0.1', 'Server');
        $this->manager->addToken('api', 'api-key');

        $status = $this->manager->getStatus();

        $this->assertTrue($status['is_active']);
        $this->assertSame('Down', $status['message']);
        $this->assertCount(1, $status['ips']);
        $this->assertCount(1, $status['tokens']);
    }
}
