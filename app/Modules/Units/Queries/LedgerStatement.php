<?php

declare(strict_types=1);

namespace App\Modules\Units\Queries;

use App\Models\User;
use App\Modules\Units\Models\UnitLedgerEntry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class LedgerStatement
{
    /**
     * @return Builder<UnitLedgerEntry>
     */
    public function query(User $user, ?string $type, ?string $from, ?string $to): Builder
    {
        $query = UnitLedgerEntry::query()->where('user_id', $user->id);

        if ($type !== null && $type !== '') {
            $query->where('type', $type);
        }

        if ($from !== null && $from !== '') {
            $query->where('created_at', '>=', Carbon::parse($from, config('app.timezone'))->startOfDay());
        }

        if ($to !== null && $to !== '') {
            $query->where('created_at', '<=', Carbon::parse($to, config('app.timezone'))->endOfDay());
        }

        return $query->orderByDesc('id');
    }
}
