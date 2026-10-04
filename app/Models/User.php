<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Modules\Announcements\Models\Announcement;
use App\Modules\Payments\Models\Payment;
use App\Modules\Referrals\Models\Referral;
use App\Modules\Units\Models\UnitLedgerEntry;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'member_no',
        'name',
        'email',
        'phone',
        'country',
        'password',
        'role',
        'status',
        'referral_code',
        'referred_by',
        'email_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (blank($user->referral_code)) {
                $user->referral_code = static::generateReferralCode();
            }

            if (blank($user->member_no)) {
                $user->member_no = 'TMP-'.strtoupper((string) Str::ulid());
            }
        });

        static::created(function (User $user): void {
            if ($user->status === UserStatus::Pending || ! str_starts_with((string) $user->member_no, 'TMP-')) {
                return;
            }

            $user->forceFill([
                'member_no' => sprintf('MM-%06d', $user->id),
            ])->saveQuietly();
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
        ];
    }

    public static function generateReferralCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $length = strlen($alphabet) - 1;

        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $alphabet[random_int(0, $length)];
            }
        } while (static::withTrashed()->where('referral_code', $code)->exists());

        return $code;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [UserRole::Admin, UserRole::SuperAdmin], true);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(self::class, 'referred_by')->withTrashed();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(UnitLedgerEntry::class);
    }

    public function referralsMade(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referralReceived(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Referral::class, 'referred_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
