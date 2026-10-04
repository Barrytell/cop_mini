<?php

declare(strict_types=1);

namespace App\Modules\Referrals\Events;

use App\Modules\Referrals\Models\Referral;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReferralRewarded
{
    use Dispatchable, SerializesModels;

    public function __construct(public Referral $referral) {}
}
