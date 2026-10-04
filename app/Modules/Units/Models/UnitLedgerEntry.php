<?php

declare(strict_types=1);

namespace App\Modules\Units\Models;

use App\Enums\LedgerType;
use App\Models\User;
use App\Modules\Units\Exceptions\ImmutableLedgerException;
use Database\Factories\UnitLedgerEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UnitLedgerEntry extends Model
{
    /** @use HasFactory<UnitLedgerEntryFactory> */
    use HasFactory;

    protected $table = 'units_ledger';

    protected $fillable = [
        'user_id',
        'units',
        'type',
        'reference_type',
        'reference_id',
        'note',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'units' => 'integer',
            'type' => LedgerType::class,
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new ImmutableLedgerException('Unit ledger entries cannot be changed.');
        });

        static::deleting(function (): void {
            throw new ImmutableLedgerException('Unit ledger entries cannot be deleted.');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory(): UnitLedgerEntryFactory
    {
        return UnitLedgerEntryFactory::new();
    }
}
