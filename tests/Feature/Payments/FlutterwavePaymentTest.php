<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Enums\UserStatus;
use App\Models\User;
use App\Modules\Payments\Models\Payment;
use App\Services\UnitBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FlutterwavePaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'flutterwave.secret_key' => 'test-secret',
            'flutterwave.secret_hash' => 'test-hash',
            'flutterwave.base_url' => 'https://api.flutterwave.com',
        ]);
    }

    public function test_a_member_is_redirected_to_flutterwave_and_the_price_is_snapshotted(): void
    {
        Http::fake([
            'https://api.flutterwave.com/v3/payments' => Http::response([
                'status' => 'success',
                'data' => ['link' => 'https://checkout.flutterwave.com/pay/test'],
            ]),
        ]);

        $member = User::factory()->pending()->create();

        $this->actingAs($member)
            ->post('/member/payments', ['amount_usd' => '10.00'])
            ->assertRedirect('https://checkout.flutterwave.com/pay/test');

        $payment = Payment::query()->where('user_id', $member->id)->first();
        $this->assertNotNull($payment);
        $this->assertSame('10.00', $payment->amount_usd);
        $this->assertSame('0.010000', $payment->unit_price_snapshot);
        $this->assertSame(1000, $payment->units_purchased);
        $this->assertSame('pending', $payment->status->value);
        $this->assertSame('initial', $payment->type->value);
    }

    public function test_the_callback_verifies_server_side_and_activates_the_member(): void
    {
        $member = User::factory()->pending()->create();
        $payment = Payment::factory()->create([
            'user_id' => $member->id,
            'tx_ref' => 'MM-CALLBACK-1',
            'units_purchased' => 1000,
        ]);

        Http::fake([
            'https://api.flutterwave.com/v3/transactions/4242/verify' => Http::response(
                '{"status":"success","data":{"id":4242,"tx_ref":"MM-CALLBACK-1","amount":"10.00","currency":"USD","status":"successful"}}',
                200,
                ['Content-Type' => 'application/json'],
            ),
        ]);

        $this->actingAs($member)
            ->get('/member/payments/callback?transaction_id=4242&tx_ref=MM-CALLBACK-1&status=successful')
            ->assertRedirect(route('member.dashboard'));

        $this->assertSame(UserStatus::Active, $member->fresh()->status);
        $this->assertSame(1000, app(UnitBalanceService::class)->balance($member));
        $this->assertSame('successful', $payment->fresh()->status->value);
    }

    public function test_webhook_rejects_a_bad_signature_and_accepts_a_valid_one(): void
    {
        $member = User::factory()->pending()->create();
        Payment::factory()->create([
            'user_id' => $member->id,
            'tx_ref' => 'MM-HOOK-1',
            'units_purchased' => 1000,
        ]);

        Http::fake([
            'https://api.flutterwave.com/v3/transactions/77/verify' => Http::response(
                '{"status":"success","data":{"id":77,"tx_ref":"MM-HOOK-1","amount":10.00,"currency":"USD","status":"successful"}}',
                200,
                ['Content-Type' => 'application/json'],
            ),
        ]);

        $this->postJson('/webhooks/flutterwave', ['data' => ['id' => 77]])->assertUnauthorized();

        $this->withHeaders(['verif-hash' => 'test-hash'])
            ->postJson('/webhooks/flutterwave', ['data' => ['id' => 77]])
            ->assertOk();

        $this->assertSame(UserStatus::Active, $member->fresh()->status);
        $this->assertSame(1000, app(UnitBalanceService::class)->balance($member));
    }

    public function test_callback_uses_the_amount_next_to_currency_not_an_earlier_amount(): void
    {
        $member = User::factory()->pending()->create();
        $payment = Payment::factory()->create([
            'user_id' => $member->id,
            'tx_ref' => 'MM-CALLBACK-2',
            'amount_usd' => '10.00',
            'units_purchased' => 1000,
        ]);

        Http::fake([
            'https://api.flutterwave.com/v3/transactions/5151/verify' => Http::response(
                '{"status":"success","meta":{"amount":1},"data":{"id":5151,"tx_ref":"MM-CALLBACK-2","amount":"10.00","currency":"USD","status":"successful","charged_amount":10.00}}',
                200,
                ['Content-Type' => 'application/json'],
            ),
        ]);

        $this->actingAs($member)
            ->get('/member/payments/callback?transaction_id=5151&tx_ref=MM-CALLBACK-2&status=successful')
            ->assertRedirect(route('member.dashboard'));

        $this->assertSame(UserStatus::Active, $member->fresh()->status);
        $this->assertSame('10.00', $payment->fresh()->amount_paid);
    }

    public function test_a_cancelled_checkout_is_recorded_as_cancelled(): void
    {
        $member = User::factory()->pending()->create();
        $payment = Payment::factory()->create([
            'user_id' => $member->id,
            'tx_ref' => 'MM-CANCEL-1',
        ]);

        $this->actingAs($member)
            ->get('/member/payments/callback?tx_ref=MM-CANCEL-1&status=cancelled')
            ->assertRedirect(route('member.activate'))
            ->assertSessionHasErrors('amount_usd');

        $this->assertSame('cancelled', $payment->fresh()->status->value);
        $this->assertSame(UserStatus::Pending, $member->fresh()->status);
        $this->assertSame(0, app(UnitBalanceService::class)->balance($member));
    }
}
