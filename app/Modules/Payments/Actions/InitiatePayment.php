<?php

declare(strict_types=1);

namespace App\Modules\Payments\Actions;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\UserStatus;
use App\Models\User;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Exceptions\PaymentGatewayException;
use App\Modules\Payments\Exceptions\PaymentGatewayTimeoutException;
use App\Modules\Payments\Models\Payment;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InitiatePayment
{
    public function __construct(private readonly PaymentGatewayInterface $gateway) {}

    public function handle(User $user, string $amountUsd, string $currency): string
    {
        if (! in_array($user->status, [UserStatus::Pending, UserStatus::Active], true)) {
            throw ValidationException::withMessages([
                'amount_usd' => 'This account cannot make a payment.',
            ]);
        }

        $currency = strtoupper($currency);
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

        $type = $user->status === UserStatus::Active ? PaymentType::TopUp : PaymentType::Initial;

        $existing = Payment::query()
            ->where('user_id', $user->id)
            ->where('status', PaymentStatus::Pending)
            ->where('amount_usd', $amount)
            ->where('charge_currency', $currency)
            ->where('type', $type)
            ->where('created_at', '>=', now()->subMinutes(20))
            ->latest('id')
            ->first();

        $existingLink = is_array($existing?->gateway_payload) ? ($existing->gateway_payload['link'] ?? null) : null;

        if (is_string($existingLink) && $existingLink !== '') {
            return $existingLink;
        }

        try {
            $chargeAmount = $this->gateway->quote($amount, $currency);
        } catch (PaymentGatewayTimeoutException $exception) {
            throw ValidationException::withMessages([
                'amount_usd' => $exception->getMessage(),
            ]);
        } catch (PaymentGatewayException $exception) {
            throw ValidationException::withMessages([
                'currency' => $exception->getMessage(),
            ]);
        }

        $payment = Payment::query()->create([
            'user_id' => $user->id,
            'tx_ref' => 'MM'.$user->id.'-'.Str::ulid(),
            'amount_usd' => $amount,
            'charge_currency' => $currency,
            'charge_amount' => $chargeAmount,
            'unit_price_snapshot' => $unitPrice,
            'units_purchased' => $units,
            'type' => $type,
            'status' => PaymentStatus::Pending,
        ]);

        $redirectUrl = (string) config('services.flutterwave.redirect_url');

        if ($redirectUrl === '') {
            $redirectUrl = route('member.payments.callback');
        }

        try {
            $initialization = $this->gateway->initialize($payment, $user, $redirectUrl);
        } catch (PaymentGatewayTimeoutException $exception) {
            throw ValidationException::withMessages([
                'amount_usd' => $exception->getMessage(),
            ]);
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

        $payment->forceFill([
            'gateway_payload' => ['link' => $initialization->redirectUrl],
        ])->save();

        return $initialization->redirectUrl;
    }
}
