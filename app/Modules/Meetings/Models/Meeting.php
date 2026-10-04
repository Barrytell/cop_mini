<?php

declare(strict_types=1);

namespace App\Modules\Meetings\Models;

use App\Enums\MeetingStatus;
use App\Models\User;
use Database\Factories\MeetingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meeting extends Model
{
    /** @use HasFactory<MeetingFactory> */
    use HasFactory;

    protected $fillable = [
        'created_by',
        'title',
        'description',
        'location',
        'meeting_url',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'status' => MeetingStatus::class,
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function newFactory(): MeetingFactory
    {
        return MeetingFactory::new();
    }
}
