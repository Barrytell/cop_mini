<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\User;
use App\Modules\Payments\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tx_ref' => 'MM-'.Str::ulid(),
            'flw_transaction_id' => null,
            'amount_usd' => '10.00',
            'currency_paid' => null,
            'amount_paid' => null,
            'unit_price_snapshot' => '0.010000',
            'units_purchased' => 1000,
            'type' => PaymentType::Initial,
            'status' => PaymentStatus::Pending,
            'gateway_payload' => null,
            'paid_at' => null,
        ];
    }

    public function successful(): static
    {
        return $this->state(fn (): array => [
            'status' => PaymentStatus::Successful,
            'flw_transaction_id' => (string) fake()->unique()->numerify('########'),
            'currency_paid' => 'USD',
            'amount_paid' => '10.00',
            'paid_at' => now(),
            'gateway_payload' => ['raw' => '{"status":"successful","amount":"10.00","currency":"USD"}'],
        ]);
    }
}
