<?php

declare(strict_types=1);

namespace App\Modules\Units\Actions;

use App\Enums\LedgerType;
use App\Models\User;
use App\Modules\Units\Models\UnitLedgerEntry;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class AppendLedgerEntry
{
    public function handle(
        User $user,
        int $units,
        LedgerType $type,
        ?Model $reference = null,
        ?string $note = null,
        ?User $actor = null,
    ): UnitLedgerEntry {
        if ($units === 0) {
            throw new InvalidArgumentException('A ledger entry must change the balance.');
        }

        return UnitLedgerEntry::query()->create([
            'user_id' => $user->id,
            'units' => $units,
            'type' => $type,
            'reference_type' => $reference ? $reference::class : null,
            'reference_id' => $reference?->getKey(),
            'note' => $note,
            'created_by' => $actor?->id,
        ]);
    }
}
