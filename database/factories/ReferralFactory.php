<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReferralStatus;
use App\Models\User;
use App\Modules\Referrals\Models\Referral;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Referral>
 */
class ReferralFactory extends Factory
{
    protected $model = Referral::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'referrer_id' => User::factory(),
            'referred_id' => User::factory(),
            'status' => ReferralStatus::Pending,
            'bonus_units' => 0,
            'rewarded_at' => null,
        ];
    }
}
