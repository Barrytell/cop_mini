<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Support\AdminPermission;

class SettingPolicy
{
    public function manage(User $user): bool
    {
        return AdminPermission::allows($user, AdminPermission::SETTINGS);
    }
}
