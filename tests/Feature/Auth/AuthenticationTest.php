<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_member_is_sent_to_the_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.com',
            'status' => UserStatus::Active,
        ]);

        $this->post('/login', [
            'email' => 'member@example.com',
            'password' => 'password',
            'remember' => '1',
        ])->assertRedirect(route('member.dashboard'))
            ->assertCookie(Auth::guard()->getRecallerName());

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_pending_member_is_sent_to_activation(): void
    {
        User::factory()->pending()->create(['email' => 'pending@example.com']);

        $this->post('/login', [
            'email' => 'pending@example.com',
            'password' => 'password',
        ])->assertRedirect(route('member.activate'));
    }

    public function test_admins_are_sent_to_the_admin_area(): void
    {
        User::factory()->admin()->create(['email' => 'admin@example.com']);
        User::factory()->superAdmin()->create(['email' => 'root@example.com']);

        $this->post('/login', ['email' => 'admin@example.com', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->post('/logout');

        $this->post('/login', ['email' => 'root@example.com', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_suspended_accounts_cannot_sign_in(): void
    {
        User::factory()->suspended()->create(['email' => 'paused@example.com']);

        $this->post('/login', [
            'email' => 'paused@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_bad_credentials_are_rejected(): void
    {
        User::factory()->create(['email' => 'member@example.com']);

        $this->post('/login', [
            'email' => 'member@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_throttled(): void
    {
        User::factory()->create(['email' => 'member@example.com']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', [
                'email' => 'member@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $this->post('/login', [
            'email' => 'member@example.com',
            'password' => 'password',
        ])->assertStatus(429);
    }

    public function test_role_gates_and_status_redirects(): void
    {
        $member = User::factory()->create();
        $pending = User::factory()->pending()->create();
        $admin = User::factory()->admin()->create([
            'permissions' => [\App\Support\AdminPermission::DASHBOARD, \App\Support\AdminPermission::SETTINGS],
        ]);

        $this->get('/member/dashboard')->assertRedirect(route('login'));
        $this->get('/admin')->assertRedirect(route('login'));

        $this->actingAs($member)->get('/admin')->assertForbidden();
        $this->actingAs($member)->get('/member/dashboard')->assertOk();

        $this->actingAs($pending)->get('/member/dashboard')->assertRedirect(route('member.activate'));
        $this->actingAs($pending)->get('/member/activate')->assertOk();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/settings')->assertOk();
        $this->actingAs($admin)->get('/admin/members')->assertForbidden();
    }

    public function test_logout_ends_the_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
