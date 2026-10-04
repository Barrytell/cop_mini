<?php

declare(strict_types=1);

namespace App\Modules\Payments\Actions;

use App\Enums\LedgerType;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\UserStatus;
use App\Models\User;
use App\Modules\Payments\Data\PaymentVerification;
use App\Modules\Payments\Events\PaymentSucceeded;
use App\Modules\Payments\Models\Payment;
use App\Modules\Referrals\Actions\RewardReferral;
use App\Modules\Units\Actions\AppendLedgerEntry;
use App\Support\Money;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class VerifyPayment
{
    public function __construct(
        private readonly AppendLedgerEntry $appendLedgerEntry,
        private readonly RewardReferral $rewardReferral,
    ) {}

    public function handle(Payment $payment, PaymentVerification $verification): Payment
    {
        try {
            return $this->apply($payment, $verification);
        } catch (UniqueConstraintViolationException $exception) {
            $fresh = $payment->fresh();

            if ($fresh && $fresh->status === PaymentStatus::Successful) {
                return $fresh;
            }

            throw $exception;
        }
    }

    private function apply(Payment $payment, PaymentVerification $verification): Payment
    {
        return DB::transaction(function () use ($payment, $verification): Payment {
            $locked = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === PaymentStatus::Successful) {
                return $locked;
            }

            // A verified transaction for a different tx_ref must not fail this row.
            if ($verification->txRef === '' || ! hash_equals($locked->tx_ref, $verification->txRef)) {
                return $locked;
            }

            // Flutterwave can still be processing. Leave the payment pending so a later
            // webhook can complete it.
            if (in_array($verification->gatewayStatus, ['pending', 'processing'], true)) {
                return $locked;
            }

            $matches = $verification->successful
                && $verification->currency === 'USD'
                && Money::compare($verification->amount, (string) $locked->amount_usd) === 0;

            if (! $matches) {
                $locked->forceFill([
                    'status' => in_array($verification->gatewayStatus, ['cancelled', 'canceled'], true)
                        ? PaymentStatus::Cancelled
                        : PaymentStatus::Failed,
                    'flw_transaction_id' => $verification->transactionId !== '' ? $verification->transactionId : null,
                    'currency_paid' => $verification->currency !== '' ? $verification->currency : null,
                    'amount_paid' => $verification->amount,
                    'gateway_payload' => ['raw' => $verification->rawBody],
                ])->save();

                return $locked->refresh();
            }

            $user = User::query()->whereKey($locked->user_id)->lockForUpdate()->firstOrFail();

            $locked->forceFill([
                'status' => PaymentStatus::Successful,
                'flw_transaction_id' => $verification->transactionId,
                'currency_paid' => $verification->currency,
                'amount_paid' => $verification->amount,
                'gateway_payload' => ['raw' => $verification->rawBody],
                'paid_at' => now(),
            ])->save();

            $this->appendLedgerEntry->handle(
                user: $user,
                units: (int) $locked->units_purchased,
                type: LedgerType::Purchase,
                reference: $locked,
                note: 'Payment '.$locked->tx_ref.' at '.$locked->unit_price_snapshot.' USD per unit',
            );

            if ($locked->type === PaymentType::Initial && $user->status === UserStatus::Pending) {
                $user->forceFill(['status' => UserStatus::Active])->save();
                $this->rewardReferral->handle($user->refresh());
            }

            DB::afterCommit(fn () => PaymentSucceeded::dispatch($locked->fresh()));

            return $locked->refresh();
        });
    }
}
