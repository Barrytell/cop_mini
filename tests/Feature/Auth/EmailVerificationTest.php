<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_signed_link_verifies_the_email_address(): void
    {
        $user = User::factory()->unverified()->pending()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHour(),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        $this->actingAs($user)->get($url)->assertRedirect(route('member.activate'));
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertSame(UserStatus::Pending, $user->fresh()->status);
    }

    public function test_a_logged_out_member_can_finish_verification_after_signing_in(): void
    {
        $user = User::factory()->unverified()->pending()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHour(),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        $this->get($url)->assertRedirect(route('login'));

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect($url);

        $this->get($url)->assertRedirect(route('member.activate'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
