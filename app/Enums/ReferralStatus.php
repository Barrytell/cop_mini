<?php

declare(strict_types=1);

namespace App\Enums;

enum ReferralStatus: string
{
    case Pending = 'pending';
    case Rewarded = 'rewarded';
}
