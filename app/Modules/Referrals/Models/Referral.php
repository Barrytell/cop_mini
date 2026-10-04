<?php

declare(strict_types=1);

namespace App\Modules\Referrals\Models;

use App\Enums\ReferralStatus;
use App\Models\User;
use Database\Factories\ReferralFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    /** @use HasFactory<ReferralFactory> */
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'status',
        'bonus_units',
        'rewarded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReferralStatus::class,
            'bonus_units' => 'integer',
            'rewarded_at' => 'datetime',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id')->withTrashed();
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id')->withTrashed();
    }

    protected static function newFactory(): ReferralFactory
    {
        return ReferralFactory::new();
    }
}
