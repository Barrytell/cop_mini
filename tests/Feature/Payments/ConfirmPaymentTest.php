<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Enums\PaymentStatus;
use App\Enums\UserStatus;
use App\Models\User;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Data\PaymentVerification;
use App\Modules\Payments\Models\Payment;
use App\Services\UnitBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FakePaymentGateway;
use Tests\TestCase;

class ConfirmPaymentTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new FakePaymentGateway;
        $this->app->instance(PaymentGatewayInterface::class, $this->gateway);
        config(['services.flutterwave.webhook_hash' => 'test-hash']);
    }

    public function test_a_verified_callback_activates_the_member_and_credits_units(): void
    {
        $member = User::factory()->pending()->create();
        $payment = $this->pendingPayment($member);

        $this->gateway->byId['42'] = $this->verification($payment, '10.50');

        $this->actingAs($member)
            ->get('/member/payments/callback?transaction_id=42&tx_ref='.$payment->tx_ref.'&status=successful')
            ->assertRedirect(route('member.dashboard'));

        $this->assertSame(UserStatus::Active, $member->fresh()->status);
        $this->assertMatchesRegularExpression('/^MM-\d{6}$/', $member->fresh()->member_no);
        $this->assertSame(1000, app(UnitBalanceService::class)->balance($member));
        $this->assertSame(PaymentStatus::Successful, $payment->fresh()->status);
    }

    public function test_a_failed_webhook_does_not_credit_units(): void
    {
        $member = User::factory()->pending()->create();
        $payment = $this->pendingPayment($member);

        $this->gateway->byId['7'] = $this->verification($payment, '10.00', successful: false, status: 'failed');

        $this->withHeaders(['verif-hash' => 'test-hash'])
            ->postJson('/webhooks/flutterwave', [
                'event' => 'charge.failed',
                'data' => ['id' => 7, 'tx_ref' => $payment->tx_ref, 'status' => 'successful'],
            ])
            ->assertOk();

        $this->assertSame(UserStatus::Pending, $member->fresh()->status);
        $this->assertSame(0, app(UnitBalanceService::class)->balance($member));
        $this->assertSame(PaymentStatus::Failed, $payment->fresh()->status);
    }

    public function test_a_duplicate_webhook_does_not_credit_units_twice(): void
    {
        $member = User::factory()->pending()->create();
        $payment = $this->pendingPayment($member);
        $this->gateway->byId['9'] = $this->verification($payment, '10.00');

        $payload = [
            'event' => 'charge.completed',
            'data' => ['id' => 9, 'tx_ref' => $payment->tx_ref],
        ];

        $this->withHeaders(['verif-hash' => 'test-hash'])
            ->postJson('/webhooks/flutterwave', $payload)
            ->assertOk();

        $this->withHeaders(['verif-hash' => 'test-hash'])
            ->postJson('/webhooks/flutterwave', $payload)
            ->assertOk();

        $this->assertSame(1000, app(UnitBalanceService::class)->balance($member));
        $this->assertSame(1, $member->ledgerEntries()->count());
    }

    public function test_an_amount_below_the_expected_charge_is_rejected(): void
    {
        $member = User::factory()->pending()->create();
        $payment = $this->pendingPayment($member);
        $this->gateway->byId['11'] = $this->verification($payment, '9.99');

        $this->withHeaders(['verif-hash' => 'test-hash'])
            ->postJson('/webhooks/flutterwave', [
                'event' => 'charge.completed',
                'data' => ['id' => 11, 'tx_ref' => $payment->tx_ref],
            ])
            ->assertOk();

        $this->assertSame(PaymentStatus::Failed, $payment->fresh()->status);
        $this->assertSame(0, app(UnitBalanceService::class)->balance($member));
        $this->assertSame(UserStatus::Pending, $member->fresh()->status);
    }

    public function test_a_tampered_callback_cannot_mark_a_payment_successful(): void
    {
        $member = User::factory()->pending()->create();
        $payment = $this->pendingPayment($member);
        $this->gateway->byId['15'] = $this->verification($payment, '10.00', txRef: 'TAMPERED-REF');

        $this->actingAs($member)
            ->get('/member/payments/callback?transaction_id=15&tx_ref='.$payment->tx_ref.'&status=successful&amount=10.00')
            ->assertRedirect(route('member.activate'))
            ->assertSessionHasErrors('amount_usd');

        $this->assertSame(PaymentStatus::Pending, $payment->fresh()->status);
        $this->assertSame(UserStatus::Pending, $member->fresh()->status);
        $this->assertSame(0, app(UnitBalanceService::class)->balance($member));
    }

    public function test_an_active_member_can_buy_more_units(): void
    {
        $member = User::factory()->create();
        $number = $member->member_no;

        $this->gateway->redirectUrl = 'https://checkout.flutterwave.com/pay/topup';

        $this->actingAs($member)
            ->post('/member/payments', [
                'amount_usd' => '10.00',
                'currency' => 'USD',
            ])
            ->assertRedirect('https://checkout.flutterwave.com/pay/topup');

        $payment = Payment::query()->where('user_id', $member->id)->firstOrFail();
        $this->assertSame('top_up', $payment->type->value);
        $this->gateway->byId['21'] = $this->verification($payment, '10.00');

        $this->actingAs($member)
            ->get('/member/payments/callback?transaction_id=21&tx_ref='.$payment->tx_ref)
            ->assertRedirect(route('member.dashboard'));

        $this->assertSame(1000, app(UnitBalanceService::class)->balance($member));
        $this->assertSame($number, $member->fresh()->member_no);
    }

    public function test_reconciliation_confirms_old_pending_payments_and_abandons_checkouts_with_no_transaction(): void
    {
        $member = User::factory()->pending()->create();
        $ready = $this->pendingPayment($member);
        $ready->forceFill(['created_at' => now()->subMinutes(15)])->save();
        $this->gateway->byReference[$ready->tx_ref] = $this->verification($ready, '10.00');

        $fresh = $this->pendingPayment($member);
        $this->gateway->byReference[$fresh->tx_ref] = $this->verification($fresh, '10.00');

        $abandoned = $this->pendingPayment($member);
        $abandoned->forceFill(['created_at' => now()->subDays(2)])->save();

        $this->artisan('payments:reconcile')->assertSuccessful();

        $this->assertSame(PaymentStatus::Successful, $ready->fresh()->status);
        $this->assertSame(PaymentStatus::Pending, $fresh->fresh()->status);
        $this->assertSame(PaymentStatus::Cancelled, $abandoned->fresh()->status);
        $this->assertSame(1000, app(UnitBalanceService::class)->balance($member));
    }

    public function test_a_member_can_download_a_pdf_receipt_for_a_confirmed_payment(): void
    {
        $member = User::factory()->create();
        $payment = Payment::factory()->successful()->create([
            'user_id' => $member->id,
        ]);

        $response = $this->actingAs($member)->get(route('member.payments.receipt', $payment));

        $response->assertOk();
        $this->assertStringContainsString('pdf', (string) $response->headers->get('content-type'));

        $this->actingAs($member)
            ->get(route('member.payments.index'))
            ->assertOk()
            ->assertSee($payment->tx_ref);
    }

    private function pendingPayment(User $member): Payment
    {
        return Payment::factory()->create([
            'user_id' => $member->id,
            'amount_usd' => '10.00',
            'charge_currency' => 'USD',
            'charge_amount' => '10.00',
            'units_purchased' => 1000,
        ]);
    }

    private function verification(
        Payment $payment,
        string $amount,
        bool $successful = true,
        string $status = 'successful',
        ?string $txRef = null,
    ): PaymentVerification {
        return new PaymentVerification(
            successful: $successful,
            transactionId: '1',
            txRef: $txRef ?? $payment->tx_ref,
            amount: $amount,
            currency: 'USD',
            rawBody: '{}',
            gatewayStatus: $status,
        );
    }
}
