<?php

declare(strict_types=1);

namespace App\Modules\Meetings\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingRsvp extends Model
{
    protected $fillable = [
        'meeting_id',
        'user_id',
        'attending',
    ];

    protected function casts(): array
    {
        return [
            'attending' => 'boolean',
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
