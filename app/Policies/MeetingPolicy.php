<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserStatus;
use App\Models\User;

class MeetingPolicy
{
    public function manage(User $user): bool
    {
        return $user->isAdmin() && $user->status === UserStatus::Active;
    }
}
