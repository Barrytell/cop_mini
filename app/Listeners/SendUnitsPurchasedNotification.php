<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Modules\Payments\Events\PaymentSucceeded;
use App\Notifications\UnitsPurchased;

class SendUnitsPurchasedNotification
{
    public function handle(PaymentSucceeded $event): void
    {
        $payment = $event->payment->loadMissing('user');
        $payment->user?->notify(new UnitsPurchased($payment));
    }
}
