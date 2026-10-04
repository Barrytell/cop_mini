<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Modules\Payments\Events\PaymentConfirmed;
use App\Notifications\UnitsPurchased;

class SendPaymentReceipt
{
    public function handle(PaymentConfirmed $event): void
    {
        $payment = $event->payment->loadMissing('user');
        $payment->user?->notify(new UnitsPurchased($payment));
    }
}
