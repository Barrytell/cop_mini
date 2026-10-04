<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Modules\Referrals\Events\ReferralRewarded;
use App\Notifications\ReferralBonusReceived;

class SendReferralBonusNotification
{
    public function handle(ReferralRewarded $event): void
    {
        $referral = $event->referral->loadMissing('referrer');

        if ((int) $referral->bonus_units < 1) {
            return;
        }

        $referral->referrer?->notify(new ReferralBonusReceived($referral));
    }
}
