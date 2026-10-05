<?php

declare(strict_types=1);

namespace App\Modules\Members\Actions;

use App\Enums\LedgerType;
use App\Models\User;
use App\Modules\Units\Actions\AppendLedgerEntry;
use App\Modules\Units\Models\UnitLedgerEntry;
use App\Services\UnitBalanceService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AdjustMemberUnits
{
    public function __construct(
        private readonly AppendLedgerEntry $appendLedgerEntry,
        private readonly UnitBalanceService $balances,
    ) {}

    public function handle(User $member, int $units, string $note, User $actor): UnitLedgerEntry
    {
        $note = trim($note);

        if ($units === 0) {
            throw new InvalidArgumentException('Choose a non-zero unit change.');
        }

        if ($note === '') {
            throw new InvalidArgumentException('A note is required for every unit adjustment.');
        }

        return DB::transaction(function () use ($member, $units, $note, $actor): UnitLedgerEntry {
            $locked = User::query()->whereKey($member->id)->lockForUpdate()->firstOrFail();
            $balance = $this->balances->balance($locked);

            if ($units < 0 && ($balance + $units) < 0) {
                throw new InvalidArgumentException('That deduction would take the balance below zero.');
            }

            return $this->appendLedgerEntry->handle(
                user: $locked,
                units: $units,
                type: LedgerType::AdminAdjustment,
                note: $note,
                actor: $actor,
            );
        });
    }
}
