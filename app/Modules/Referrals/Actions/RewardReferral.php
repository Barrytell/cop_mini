<?php

declare(strict_types=1);

namespace App\Modules\Referrals\Actions;

use App\Enums\LedgerType;
use App\Enums\ReferralStatus;
use App\Models\User;
use App\Modules\Referrals\Events\ReferralRewarded;
use App\Modules\Referrals\Models\Referral;
use App\Modules\Units\Actions\AppendLedgerEntry;
use Illuminate\Support\Facades\DB;

class RewardReferral
{
    public function __construct(private readonly AppendLedgerEntry $appendLedgerEntry) {}

    public function handle(User $referred): ?Referral
    {
        return DB::transaction(function () use ($referred): ?Referral {
            $referral = Referral::query()
                ->where('referred_id', $referred->id)
                ->lockForUpdate()
                ->first();

            if ($referral === null || $referral->status === ReferralStatus::Rewarded) {
                return $referral;
            }

            $referrer = User::query()->whereKey($referral->referrer_id)->lockForUpdate()->first();

            if ($referrer === null) {
                return $referral;
            }

            $bonus = (int) setting('referral_bonus_units', config('minimini.defaults.referral_bonus_units'));

            if ($bonus > 0) {
                $this->appendLedgerEntry->handle(
                    user: $referrer,
                    units: $bonus,
                    type: LedgerType::ReferralBonus,
                    reference: $referral,
                    note: 'Referral bonus for '.$referred->member_no,
                );
            }

            $referral->forceFill([
                'status' => ReferralStatus::Rewarded,
                'bonus_units' => $bonus,
                'rewarded_at' => now(),
            ])->save();

            DB::afterCommit(fn () => ReferralRewarded::dispatch($referral));

            return $referral->refresh();
        });
    }
}
