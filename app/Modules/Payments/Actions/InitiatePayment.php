<?php

declare(strict_types=1);

namespace App\Modules\Payments\Actions;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\UserStatus;
use App\Models\User;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Exceptions\PaymentGatewayException;
use App\Modules\Payments\Models\Payment;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InitiatePayment
{
    public function __construct(private readonly PaymentGatewayInterface $gateway) {}

    public function handle(User $user, string $amountUsd): string
    {
        if (! in_array($user->status, [UserStatus::Pending, UserStatus::Active], true)) {
            throw ValidationException::withMessages([
                'amount_usd' => 'This account cannot make a payment.',
            ]);
        }

        $unitPrice = Money::normalize((string) setting('unit_price_usd', '0.01'), 6);
        $amount = Money::normalize($amountUsd, 2);
        $minimum = Money::normalize((string) setting('min_payment_usd', '10.00'), 2);

        if (Money::compare($amount, $minimum) < 0) {
            throw ValidationException::withMessages([
                'amount_usd' => "The minimum payment is {$minimum} USD.",
            ]);
        }

        $units = Money::unitsFrom($amount, $unitPrice);

        if ($units < 1) {
            throw ValidationException::withMessages([
                'amount_usd' => 'This amount buys zero units at the current unit price.',
            ]);
        }

        $payment = Payment::query()->create([
            'user_id' => $user->id,
            'tx_ref' => 'MM'.$user->id.'-'.Str::ulid(),
            'amount_usd' => $amount,
            'unit_price_snapshot' => $unitPrice,
            'units_purchased' => $units,
            'type' => $user->status === UserStatus::Active ? PaymentType::TopUp : PaymentType::Initial,
            'status' => PaymentStatus::Pending,
        ]);

        try {
            $initialization = $this->gateway->initialize(
                $payment,
                $user,
                route('member.payments.callback'),
            );
        } catch (PaymentGatewayException $exception) {
            DB::transaction(function () use ($payment, $exception): void {
                $locked = Payment::query()->whereKey($payment->id)->lockForUpdate()->first();
                if ($locked && $locked->status === PaymentStatus::Pending) {
                    $locked->forceFill([
                        'status' => PaymentStatus::Failed,
                        'gateway_payload' => ['error' => $exception->getMessage()],
                    ])->save();
                }
            });

            throw ValidationException::withMessages([
                'amount_usd' => $exception->getMessage(),
            ]);
        }

        return $initialization->redirectUrl;
    }
}
