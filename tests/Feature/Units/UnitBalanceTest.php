<?php

declare(strict_types=1);

namespace Tests\Feature\Units;

use App\Enums\LedgerType;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\ReferralStatus;
use App\Enums\UserStatus;
use App\Models\User;
use App\Modules\Payments\Actions\ConfirmPayment;
use App\Modules\Payments\Data\PaymentVerification;
use App\Modules\Payments\Exceptions\ImmutablePaymentException;
use App\Modules\Payments\Models\Payment;
use App\Modules\Referrals\Actions\AttachReferral;
use App\Modules\Units\Actions\AppendLedgerEntry;
use App\Modules\Units\Exceptions\ImmutableLedgerException;
use App\Services\SettingsService;
use App\Services\UnitBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_balance_is_the_sum_of_append_only_entries(): void
    {
        $user = User::factory()->create();
        $ledger = app(AppendLedgerEntry::class);

        $ledger->handle($user, 100, LedgerType::AdminAdjustment, note: 'Opening');
        $ledger->handle($user, -40, LedgerType::AdminAdjustment, note: 'Correction');
        $ledger->handle($user, 2, LedgerType::AdminAdjustment, note: 'Courtesy');

        $this->assertSame(62, app(UnitBalanceService::class)->balance($user));
        $this->assertSame(3, $user->ledgerEntries()->count());
    }

    public function test_ledger_rows_cannot_be_edited_or_deleted(): void
    {
        $entry = app(AppendLedgerEntry::class)->handle(
            User::factory()->create(),
            10,
            LedgerType::AdminAdjustment,
            note: 'Locked',
        );

        $this->expectException(ImmutableLedgerException::class);
        $entry->update(['units' => 99]);
    }

    public function test_ledger_rows_cannot_be_removed(): void
    {
        $entry = app(AppendLedgerEntry::class)->handle(
            User::factory()->create(),
            10,
            LedgerType::AdminAdjustment,
            note: 'Locked',
        );

        $this->expectException(ImmutableLedgerException::class);
        $entry->delete();
    }

    public function test_verified_payment_credits_the_snapshotted_units_and_activates_once(): void
    {
        $referrer = User::factory()->create();
        $member = User::factory()->pending()->create();
        app(AttachReferral::class)->handle($member, $referrer->referral_code);

        app(SettingsService::class)->put('unit_price_usd', '0.500000');
        app(SettingsService::class)->put('referral_bonus_units', 2);

        $payment = Payment::factory()->create([
            'user_id' => $member->id,
            'amount_usd' => '10.00',
            'unit_price_snapshot' => '0.010000',
            'units_purchased' => 1000,
            'type' => PaymentType::Initial,
            'status' => PaymentStatus::Pending,
        ]);

        $confirmed = app(ConfirmPayment::class)->handle($payment, $this->verification($payment));

        $this->assertSame(PaymentStatus::Successful, $confirmed->status);
        $this->assertSame(UserStatus::Active, $member->fresh()->status);
        $this->assertMatchesRegularExpression('/^MM-\d{6}$/', $member->fresh()->member_no);
        $this->assertSame(1000, app(UnitBalanceService::class)->balance($member));
        $this->assertSame(2, app(UnitBalanceService::class)->balance($referrer));
        $this->assertSame(ReferralStatus::Rewarded, $member->referralReceived->status);
        $this->assertSame('0.010000', $confirmed->unit_price_snapshot);

        $topUp = Payment::factory()->create([
            'user_id' => $member->id,
            'amount_usd' => '10.00',
            'unit_price_snapshot' => '0.010000',
            'units_purchased' => 1000,
            'type' => PaymentType::TopUp,
            'status' => PaymentStatus::Pending,
        ]);

        app(ConfirmPayment::class)->handle($topUp, $this->verification($topUp));

        $this->assertSame(2000, app(UnitBalanceService::class)->balance($member->fresh()));
        $this->assertSame(2, app(UnitBalanceService::class)->balance($referrer->fresh()));
        $this->assertSame(1, $referrer->ledgerEntries()->where('type', LedgerType::ReferralBonus)->count());
    }

    public function test_a_repeated_verification_does_not_credit_units_again(): void
    {
        $member = User::factory()->pending()->create();
        $payment = Payment::factory()->create([
            'user_id' => $member->id,
            'type' => PaymentType::Initial,
            'units_purchased' => 1000,
        ]);

        $action = app(ConfirmPayment::class);
        $verification = $this->verification($payment);
        $action->handle($payment, $verification);
        $action->handle($payment->fresh(), $verification);

        $this->assertSame(1000, app(UnitBalanceService::class)->balance($member));
        $this->assertSame(1, $member->ledgerEntries()->count());
    }

    public function test_a_still_pending_gateway_status_does_not_fail_or_credit_the_payment(): void
    {
        $member = User::factory()->pending()->create();
        $payment = Payment::factory()->create([
            'user_id' => $member->id,
            'units_purchased' => 1000,
        ]);

        $result = app(ConfirmPayment::class)->handle($payment, new PaymentVerification(
            successful: false,
            transactionId: '9002',
            txRef: $payment->tx_ref,
            amount: '10.00',
            currency: 'USD',
            rawBody: '{}',
            gatewayStatus: 'pending',
        ));

        $this->assertSame(PaymentStatus::Pending, $result->status);
        $this->assertSame(0, app(UnitBalanceService::class)->balance($member));
    }

    public function test_a_verification_for_a_different_reference_leaves_the_payment_pending(): void
    {
        $member = User::factory()->pending()->create();
        $payment = Payment::factory()->create([
            'user_id' => $member->id,
            'units_purchased' => 1000,
        ]);

        $result = app(ConfirmPayment::class)->handle($payment, new PaymentVerification(
            successful: true,
            transactionId: '9003',
            txRef: 'SOME-OTHER-REF',
            amount: '10.00',
            currency: 'USD',
            rawBody: '{}',
            gatewayStatus: 'successful',
        ));

        $this->assertSame(PaymentStatus::Pending, $result->status);
        $this->assertSame(UserStatus::Pending, $member->fresh()->status);
        $this->assertSame(0, app(UnitBalanceService::class)->balance($member));
    }

    public function test_the_dashboard_still_renders_after_a_referred_member_is_deleted(): void
    {
        $referrer = User::factory()->create();
        $member = User::factory()->create();
        app(AttachReferral::class)->handle($member, $referrer->referral_code);
        $member->delete();

        $this->actingAs($referrer)
            ->get('/member/dashboard')
            ->assertOk()
            ->assertSee($member->name);
    }

    public function test_successful_payments_cannot_be_rewritten(): void
    {
        $payment = Payment::factory()->successful()->create();

        $this->expectException(ImmutablePaymentException::class);
        $payment->update(['amount_usd' => '1.00']);
    }

    private function verification(Payment $payment): PaymentVerification
    {
        return new PaymentVerification(
            successful: true,
            transactionId: '9001',
            txRef: $payment->tx_ref,
            amount: '10.00',
            currency: 'USD',
            rawBody: '{"amount":"10.00"}',
        );
    }
}
