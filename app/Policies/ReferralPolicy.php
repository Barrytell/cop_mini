<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserStatus;
use App\Models\User;
use App\Modules\Referrals\Models\Referral;

class ReferralPolicy
{
    public function view(User $user, Referral $referral): bool
    {
        return $user->id === $referral->referrer_id
            || $user->id === $referral->referred_id
            || ($user->isAdmin() && $user->status === UserStatus::Active);
    }
}
