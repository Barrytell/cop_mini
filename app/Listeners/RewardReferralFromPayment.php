<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\PaymentType;
use App\Modules\Payments\Events\PaymentConfirmed;
use App\Modules\Referrals\Actions\RewardReferral;

class RewardReferralFromPayment
{
    public function __construct(private readonly RewardReferral $rewardReferral) {}

    public function handle(PaymentConfirmed $event): void
    {
        $payment = $event->payment->loadMissing('user');

        if ($payment->type !== PaymentType::Initial || $payment->user === null) {
            return;
        }

        $this->rewardReferral->handle($payment->user);
    }
}
