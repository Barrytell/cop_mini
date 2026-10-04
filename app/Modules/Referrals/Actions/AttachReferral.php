<?php

declare(strict_types=1);

namespace App\Modules\Referrals\Actions;

use App\Enums\ReferralStatus;
use App\Models\User;
use App\Modules\Referrals\Models\Referral;
use Illuminate\Validation\ValidationException;

class AttachReferral
{
    public function handle(User $member, ?string $referralCode): ?Referral
    {
        if ($referralCode === null || $referralCode === '') {
            return null;
        }

        $code = strtoupper(trim($referralCode));
        $referrer = User::query()->where('referral_code', $code)->first();

        if ($referrer === null) {
            throw ValidationException::withMessages([
                'referral_code' => 'That referral code was not found.',
            ]);
        }

        if ($referrer->id === $member->id) {
            throw ValidationException::withMessages([
                'referral_code' => 'You cannot use your own referral code.',
            ]);
        }

        $member->forceFill(['referred_by' => $referrer->id])->save();

        return Referral::query()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $member->id,
            'status' => ReferralStatus::Pending,
            'bonus_units' => 0,
        ]);
    }
}
