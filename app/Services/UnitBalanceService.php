<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Modules\Units\Models\UnitLedgerEntry;

class UnitBalanceService
{
    public function balance(User $user): int
    {
        return (int) UnitLedgerEntry::query()->where('user_id', $user->id)->sum('units');
    }
}
