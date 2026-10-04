<?php

declare(strict_types=1);

namespace App\Enums;

enum LedgerType: string
{
    case Purchase = 'purchase';
    case ReferralBonus = 'referral_bonus';
    case AdminAdjustment = 'admin_adjustment';
}
