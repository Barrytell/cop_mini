<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_member_can_request_and_use_a_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'member@example.com']);

        $this->post('/forgot-password', ['email' => 'member@example.com'])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user): bool {
            $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'NewPassword1',
                'password_confirmation' => 'NewPassword1',
            ])->assertRedirect(route('login'));

            return true;
        });

        $this->assertTrue(password_verify('NewPassword1', $user->fresh()->password));
        $this->assertNotSame($user->remember_token, $user->fresh()->remember_token);
    }

    public function test_an_unknown_email_gets_the_same_response_as_a_real_one(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'missing@example.com'])
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }
}
