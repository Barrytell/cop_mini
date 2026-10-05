<?php

declare(strict_types=1);

namespace Tests\Feature\Referrals;

use App\Enums\LedgerType;
use App\Enums\PaymentType;
use App\Enums\ReferralStatus;
use App\Models\User;
use App\Modules\Payments\Actions\ConfirmPayment;
use App\Modules\Payments\Data\PaymentVerification;
use App\Modules\Payments\Models\Payment;
use App\Modules\Referrals\Actions\AttachReferral;
use App\Modules\Referrals\Actions\RewardReferral;
use App\Modules\Referrals\Models\Referral;
use App\Services\SettingsService;
use App\Services\UnitBalanceService;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReferralRewardTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_public_page_stores_a_referral_code_for_thirty_days_and_registration_does_not_pay_the_bonus(): void
    {
        Notification::fake();
        $referrer = User::factory()->create();

        $visit = $this->get('/?ref='.$referrer->referral_code);

        $visit->assertOk()->assertCookie('referral_code', $referrer->referral_code);
        $cookie = collect($visit->headers->getCookies())->first(
            fn ($cookie): bool => $cookie->getName() === 'referral_code',
        );
        $this->assertNotNull($cookie);
        $this->assertEqualsWithDelta(60 * 24 * 30, ($cookie->getExpiresTime() - time()) / 60, 2);

        $this->post('/register', $this->payload())
            ->assertRedirect(route('member.activate'));

        $member = User::query()->where('email', 'invited.member@example.com')->firstOrFail();
        $this->assertSame($referrer->id, $member->referred_by);
        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referred_id' => $member->id,
            'status' => ReferralStatus::Pending->value,
            'bonus_units' => 0,
        ]);
        $this->assertSame(0, app(UnitBalanceService::class)->balance($referrer));
        Notification::assertSentTo($member, VerifyEmail::class);
    }

    public function test_an_unknown_code_a_webhook_and_a_members_own_code_are_not_stored(): void
    {
        $member = User::factory()->create();

        $this->get('/?ref=NOTACODE')->assertCookieMissing('referral_code');
        $this->get('/webhooks/flutterwave?ref='.$member->referral_code)->assertCookieMissing('referral_code');

        $this->actingAs($member)
            ->get('/?ref='.$member->referral_code)
            ->assertCookieMissing('referral_code')
            ->assertSessionMissing('referral_code');
    }

    public function test_the_bonus_is_paid_once_when_the_first_payment_is_confirmed(): void
    {
        $referrer = User::factory()->create();
        $member = User::factory()->pending()->create();
        app(AttachReferral::class)->handle($member, $referrer->referral_code);
        app(SettingsService::class)->put('referral_bonus_units', 5);

        app(RewardReferral::class)->handle($member);

        $this->assertSame(ReferralStatus::Pending, $member->referralReceived->fresh()->status);
        $this->assertSame(0, app(UnitBalanceService::class)->balance($referrer));

        $payment = Payment::factory()->create([
            'user_id' => $member->id,
            'units_purchased' => 1000,
            'type' => PaymentType::Initial,
            'status' => \App\Enums\PaymentStatus::Pending,
        ]);

        app(ConfirmPayment::class)->handle($payment, $this->verification($payment));

        $this->assertSame(5, app(UnitBalanceService::class)->balance($referrer));
        $this->assertSame(ReferralStatus::Rewarded, $member->referralReceived->fresh()->status);
        $this->assertSame(5, $member->referralReceived->fresh()->bonus_units);
        $this->assertSame(1, $referrer->ledgerEntries()->where('type', LedgerType::ReferralBonus)->count());

        app(RewardReferral::class)->handle($member->fresh());

        $topUp = Payment::factory()->create([
            'user_id' => $member->id,
            'units_purchased' => 1000,
            'type' => PaymentType::TopUp,
            'status' => \App\Enums\PaymentStatus::Pending,
        ]);
        app(ConfirmPayment::class)->handle($topUp, $this->verification($topUp));

        $this->assertSame(5, app(UnitBalanceService::class)->balance($referrer->fresh()));
        $this->assertSame(1, $referrer->ledgerEntries()->where('type', LedgerType::ReferralBonus)->count());
    }

    public function test_self_referral_and_a_second_attach_are_blocked(): void
    {
        $member = User::factory()->create();

        try {
            app(AttachReferral::class)->handle($member, $member->referral_code);
            $this->fail('A member was allowed to use their own referral code.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('referral_code', $exception->errors());
        }

        $referrer = User::factory()->create();
        $other = User::factory()->create();
        $invited = User::factory()->pending()->create();
        $first = app(AttachReferral::class)->handle($invited, $referrer->referral_code);
        $second = app(AttachReferral::class)->handle($invited, $other->referral_code);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Referral::query()->where('referred_id', $invited->id)->count());
        $this->assertSame($referrer->id, $invited->fresh()->referred_by);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Invited Member',
            'email' => 'invited.member@example.com',
            'phone' => '+15551230001',
            'country' => 'United States',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ], $overrides);
    }

    private function verification(Payment $payment): PaymentVerification
    {
        return new PaymentVerification(
            successful: true,
            transactionId: '9100',
            txRef: $payment->tx_ref,
            amount: '10.00',
            currency: 'USD',
            rawBody: '{"amount":"10.00"}',
        );
    }
}
