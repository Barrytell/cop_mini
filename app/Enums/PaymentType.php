<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentType: string
{
    case Initial = 'initial';
    case TopUp = 'top_up';
}
