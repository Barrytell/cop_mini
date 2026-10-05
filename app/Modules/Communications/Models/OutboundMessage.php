<?php

declare(strict_types=1);

namespace App\Modules\Communications\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutboundMessage extends Model
{
    protected $fillable = [
        'channel',
        'subject',
        'body',
        'audience',
        'meta',
        'created_by',
        'recipient_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'recipient_count' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
