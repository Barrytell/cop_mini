<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\AuditLog;
use App\Models\User;
use App\Modules\Payments\Models\Payment;
use App\Modules\Settings\Models\SettingChange;
use App\Support\AdminPermission;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_the_dashboard(): void
    {
        $this->seed(SettingsSeeder::class);
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Revenue today');
    }

    public function test_members_cannot_open_admin_routes(): void
    {
        $this->seed(SettingsSeeder::class);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_without_payments_permission_is_blocked(): void
    {
        $this->seed(SettingsSeeder::class);
        $admin = User::factory()->admin()->create([
            'permissions' => [AdminPermission::DASHBOARD],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.payments.index'))
            ->assertForbidden();
    }

    public function test_unit_settings_write_is_audited_and_historied(): void
    {
        $this->seed(SettingsSeeder::class);
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->put(route('admin.units.update'), [
                'unit_price_usd' => '0.05',
                'min_payment_usd' => '15.00',
                'referral_bonus_units' => 4,
                'registration_open' => '1',
                'referral_program_enabled' => '1',
            ])->assertRedirect();

        $this->assertSame('0.050000', setting('unit_price_usd'));
        $this->assertTrue(SettingChange::query()->where('key', 'unit_price_usd')->exists());
        $this->assertTrue(AuditLog::query()->where('action', 'admin.settings.units_updated')->exists());
    }

    public function test_member_soft_delete_and_restore_are_audited(): void
    {
        $this->seed(SettingsSeeder::class);
        $admin = User::factory()->superAdmin()->create();
        $member = User::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.members.destroy', $member), ['reason' => 'Duplicate account'])
            ->assertRedirect(route('admin.members.index'));

        $this->assertSoftDeleted($member);

        $this->actingAs($admin)
            ->post(route('admin.members.restore', $member), ['reason' => 'Restored after review'])
            ->assertRedirect(route('admin.members.show', $member));

        $this->assertNull($member->fresh()->deleted_at);
        $this->assertTrue(AuditLog::query()->where('action', 'admin.member.deleted')->exists());
        $this->assertTrue(AuditLog::query()->where('action', 'admin.member.restored')->exists());
    }

    public function test_only_super_admin_can_impersonate(): void
    {
        $this->seed(SettingsSeeder::class);
        $admin = User::factory()->admin()->create([
            'permissions' => [AdminPermission::MEMBERS, AdminPermission::DASHBOARD],
        ]);
        $super = User::factory()->superAdmin()->create();
        $member = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.members.impersonate', $member))
            ->assertForbidden();

        $this->actingAs($super)
            ->post(route('admin.members.impersonate', $member))
            ->assertRedirect(route('member.dashboard'));

        $this->assertAuthenticatedAs($member);
        $this->assertTrue(AuditLog::query()->where('action', 'admin.impersonation.started')->exists());
    }

    public function test_payments_index_exports_csv(): void
    {
        $this->seed(SettingsSeeder::class);
        $admin = User::factory()->superAdmin()->create();
        $member = User::factory()->create();

        Payment::factory()->create([
            'user_id' => $member->id,
            'status' => PaymentStatus::Successful,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.payments.index', ['export' => 'csv']))
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_regular_admin_defaults_to_dashboard_and_support_only(): void
    {
        $this->seed(SettingsSeeder::class);
        $admin = User::factory()->admin()->create(['permissions' => []]);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.support.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.members.index'))->assertForbidden();
        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertSame(UserStatus::Active, $admin->status);
    }
}
