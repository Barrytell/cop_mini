<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserStatus;
use App\Models\User;
use App\Modules\Units\Models\UnitLedgerEntry;

class UnitLedgerPolicy
{
    public function view(User $user, UnitLedgerEntry $entry): bool
    {
        return $user->id === $entry->user_id || ($user->isAdmin() && $user->status === UserStatus::Active);
    }

    public function adjust(User $user): bool
    {
        return $user->isAdmin() && $user->status === UserStatus::Active;
    }
}
