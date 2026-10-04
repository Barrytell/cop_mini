<?php

declare(strict_types=1);

namespace App\Enums;

enum MeetingStatus: string
{
    case Scheduled = 'scheduled';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
}
