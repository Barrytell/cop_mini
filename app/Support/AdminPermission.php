<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;

class AdminPermission
{
    public const DASHBOARD = 'dashboard';

    public const MEMBERS = 'members';

    public const PAYMENTS = 'payments';

    public const SETTINGS = 'settings';

    public const REFERRALS = 'referrals';

    public const COMMUNICATIONS = 'communications';

    public const CONTENT = 'content';

    public const BANNERS = 'banners';

    public const SUPPORT = 'support';

    public const ADMINS = 'admins';

    public const REPORTS = 'reports';

    public const SYSTEM = 'system';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::DASHBOARD,
            self::MEMBERS,
            self::PAYMENTS,
            self::SETTINGS,
            self::REFERRALS,
            self::COMMUNICATIONS,
            self::CONTENT,
            self::BANNERS,
            self::SUPPORT,
            self::ADMINS,
            self::REPORTS,
            self::SYSTEM,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::DASHBOARD => 'Dashboard',
            self::MEMBERS => 'Members',
            self::PAYMENTS => 'Payments',
            self::SETTINGS => 'Unit price and settings',
            self::REFERRALS => 'Referrals',
            self::COMMUNICATIONS => 'Communications',
            self::CONTENT => 'CMS content',
            self::BANNERS => 'Banners',
            self::SUPPORT => 'Support and contact',
            self::ADMINS => 'Admin users',
            self::REPORTS => 'Reports',
            self::SYSTEM => 'System health',
        ];
    }

    public static function allows(User $user, string $permission): bool
    {
        if ($user->status !== UserStatus::Active || ! $user->isAdmin()) {
            return false;
        }

        if ($user->role === UserRole::SuperAdmin) {
            return true;
        }

        $granted = $user->permissions;

        if (! is_array($granted) || $granted === []) {
            return in_array($permission, [self::DASHBOARD, self::SUPPORT], true);
        }

        return in_array($permission, $granted, true) || in_array('*', $granted, true);
    }
}
