<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\SettingsService;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsAndAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_are_cached_and_readable_through_the_helper(): void
    {
        $this->seed(SettingsSeeder::class);

        $this->assertSame('0.01', setting('unit_price_usd'));
        $this->assertSame(2, setting('referral_bonus_units'));
        $this->assertSame('10.00', setting('min_payment_usd'));
        $this->assertSame('minimini.org', setting('site_name'));

        app(SettingsService::class)->put('site_name', 'Minimini Cooperative');

        $this->assertSame('Minimini Cooperative', setting('site_name'));
    }

    public function test_a_missing_setting_uses_the_config_default(): void
    {
        $this->assertSame('minimini.org', setting('site_name'));
        $this->assertSame('0.01', setting('unit_price_usd'));
        $this->assertSame(2, setting('referral_bonus_units'));
    }

    public function test_admin_setting_changes_are_written_to_the_audit_log(): void
    {
        $this->seed(SettingsSeeder::class);
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->put('/admin/settings', [
                'site_name' => 'Minimini Cooperative',
                'contact_email' => 'hello@minimini.org',
                'unit_price_usd' => '0.02',
                'min_payment_usd' => '25.00',
                'referral_bonus_units' => 3,
                'social_facebook' => 'https://facebook.com/minimini',
                'social_x' => '',
                'social_instagram' => '',
                'social_linkedin' => '',
                'social_youtube' => '',
            ])->assertRedirect(route('admin.settings.edit'));

        $this->assertSame('0.020000', setting('unit_price_usd'));
        $this->assertSame(3, setting('referral_bonus_units'));

        $log = AuditLog::query()->where('action', 'settings.updated')->first();
        $this->assertNotNull($log);
        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame('203.0.113.10', $log->ip_address);
        $this->assertSame('0.01', $log->old_values['unit_price_usd']);
        $this->assertSame('0.020000', $log->new_values['unit_price_usd']);

        $this->expectException(\RuntimeException::class);
        $log->update(['action' => 'tampered']);
    }

    public function test_members_cannot_edit_settings(): void
    {
        $this->seed(SettingsSeeder::class);

        $this->actingAs(User::factory()->create())
            ->put('/admin/settings', ['site_name' => 'Hijack'])
            ->assertForbidden();
    }

    public function test_social_links_must_be_http_urls(): void
    {
        $this->seed(SettingsSeeder::class);

        $this->actingAs(User::factory()->superAdmin()->create())
            ->put('/admin/settings', [
                'site_name' => 'minimini.org',
                'contact_email' => 'hello@minimini.org',
                'unit_price_usd' => '0.01',
                'min_payment_usd' => '10.00',
                'referral_bonus_units' => 2,
                'social_facebook' => 'javascript://example.com/%0aalert(1)',
                'social_x' => '',
                'social_instagram' => '',
                'social_linkedin' => '',
                'social_youtube' => '',
            ])->assertSessionHasErrors('social_facebook');
    }
}
