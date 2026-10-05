<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Modules\Payments\Actions\ConfirmPayment;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Exceptions\PaymentGatewayException;
use App\Modules\Payments\Exceptions\PaymentGatewayTimeoutException;
use App\Modules\Payments\Exceptions\PaymentNotFoundException;
use App\Modules\Payments\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile';

    protected $description = 'Re-verify Flutterwave payments that are still pending after 10 minutes';

    public function handle(PaymentGatewayInterface $gateway, ConfirmPayment $confirmPayment): int
    {
        $confirmed = 0;
        $failed = 0;
        $abandoned = 0;
        $waiting = 0;

        Payment::query()
            ->where('status', PaymentStatus::Pending)
            ->where('created_at', '<=', now()->subMinutes(10))
            ->chunkById(50, function ($payments) use ($gateway, $confirmPayment, &$confirmed, &$failed, &$abandoned, &$waiting): void {
                foreach ($payments as $payment) {
                    try {
                        $verification = $payment->flw_transaction_id
                            ? $gateway->verify((string) $payment->flw_transaction_id)
                            : $gateway->verifyByReference($payment->tx_ref);
                        $result = $confirmPayment->handle($payment, $verification);

                        if ($result->status === PaymentStatus::Successful) {
                            $confirmed++;
                        } elseif (in_array($result->status, [PaymentStatus::Failed, PaymentStatus::Cancelled], true)) {
                            $failed++;
                        } else {
                            $waiting++;
                        }
                    } catch (PaymentNotFoundException) {
                        if ($payment->created_at->lte(now()->subDay()) && $this->abandon($payment)) {
                            $abandoned++;
                        } else {
                            $waiting++;
                        }
                    } catch (PaymentGatewayTimeoutException) {
                        $waiting++;
                    } catch (PaymentGatewayException $exception) {
                        $this->warn($payment->tx_ref.': '.$exception->getMessage());
                        $waiting++;
                    }
                }
            });

        cache()->put('payments.last_reconcile_at', now()->toIso8601String());

        $this->info("Confirmed {$confirmed}, failed {$failed}, abandoned {$abandoned}, still pending {$waiting}.");

        return self::SUCCESS;
    }

    private function abandon(Payment $payment): bool
    {
        return DB::transaction(function () use ($payment): bool {
            $locked = Payment::query()->whereKey($payment->id)->lockForUpdate()->first();

            if ($locked === null || $locked->status !== PaymentStatus::Pending) {
                return false;
            }

            $payload = is_array($locked->gateway_payload) ? $locked->gateway_payload : [];
            $payload['abandoned'] = true;

            $locked->forceFill([
                'status' => PaymentStatus::Cancelled,
                'gateway_payload' => $payload,
            ])->save();

            return true;
        });
    }
}
