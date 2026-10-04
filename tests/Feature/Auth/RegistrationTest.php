<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\ReferralStatus;
use App\Enums\UserStatus;
use App\Models\User;
use App\Services\UnitBalanceService;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_register_as_a_pending_member(): void
    {
        Notification::fake();

        $response = $this->post('/register', $this->payload());

        $response->assertRedirect(route('member.activate'));
        $this->assertAuthenticated();

        $user = User::query()->where('email', 'new.member@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(UserStatus::Pending, $user->status);
        $this->assertSame('member', $user->role->value);
        $this->assertMatchesRegularExpression('/^MM-\d{6}$/', $user->member_no);
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $user->referral_code);
        $this->assertTrue(password_verify('Password1', $user->password));
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_referral_code_from_the_query_string_is_stored_and_attached_without_a_bonus(): void
    {
        Notification::fake();
        $referrer = User::factory()->create();

        $this->get('/register?ref='.$referrer->referral_code)
            ->assertOk()
            ->assertCookie('referral_code', $referrer->referral_code);

        $this->post('/register', $this->payload(['referral_code' => null]))
            ->assertRedirect(route('member.activate'));

        $member = User::query()->where('email', 'new.member@example.com')->firstOrFail();
        $this->assertSame($referrer->id, $member->referred_by);
        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referred_id' => $member->id,
            'status' => ReferralStatus::Pending->value,
            'bonus_units' => 0,
        ]);
        $this->assertSame(0, app(UnitBalanceService::class)->balance($referrer));
    }

    public function test_an_unknown_referral_code_is_rejected(): void
    {
        $this->post('/register', $this->payload(['referral_code' => 'NOPE1234']))
            ->assertSessionHasErrors('referral_code');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_honeypot_blocks_spam_signups(): void
    {
        $this->post('/register', $this->payload(['website' => 'https://spam.example']))
            ->assertSessionHasErrors('website');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_requires_valid_input(): void
    {
        $this->post('/register', [])->assertSessionHasErrors(['name', 'email', 'phone', 'country', 'password']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        $payload = [
            'name' => 'New Member',
            'email' => 'new.member@example.com',
            'phone' => '+15551230000',
            'country' => 'United States',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'referral_code' => '',
        ];

        foreach ($overrides as $key => $value) {
            if ($value === null) {
                unset($payload[$key]);
            } else {
                $payload[$key] = $value;
            }
        }

        return $payload;
    }
}
