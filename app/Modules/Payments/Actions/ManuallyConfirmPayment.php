<?php

declare(strict_types=1);

namespace App\Modules\Payments\Actions;

use App\Modules\Payments\Data\PaymentVerification;
use App\Modules\Payments\Models\Payment;
use App\Support\Money;

class ManuallyConfirmPayment
{
    public function __construct(private readonly ConfirmPayment $confirmPayment) {}

    public function handle(Payment $payment, string $reason): Payment
    {
        $currency = strtoupper((string) ($payment->charge_currency ?: 'USD'));
        $amount = $payment->charge_amount !== null && (string) $payment->charge_amount !== ''
            ? Money::normalize((string) $payment->charge_amount, 2)
            : Money::normalize((string) $payment->amount_usd, 2);

        $raw = json_encode([
            'manual' => true,
            'reason' => $reason,
            'confirmed_at' => now()->toIso8601String(),
        ], JSON_THROW_ON_ERROR);

        return $this->confirmPayment->handle($payment, new PaymentVerification(
            successful: true,
            transactionId: $payment->flw_transaction_id ?: 'MANUAL-'.$payment->id,
            txRef: $payment->tx_ref,
            amount: $amount,
            currency: $currency,
            rawBody: $raw,
            gatewayStatus: 'successful',
        ));
    }
}
