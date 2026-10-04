<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\UserStatus;
use App\Models\User;

class RedirectsAuthenticatedUser
{
    public function url(User $user): string
    {
        if ($user->isAdmin()) {
            return route('admin.dashboard');
        }

        return match ($user->status) {
            UserStatus::Suspended => route('account.suspended'),
            UserStatus::Pending => route('member.activate'),
            UserStatus::Active => route('member.dashboard'),
        };
    }
}
