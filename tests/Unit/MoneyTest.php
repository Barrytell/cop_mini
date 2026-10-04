<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_units_are_floored_from_decimal_strings(): void
    {
        $this->assertSame(1000, Money::unitsFrom('10.00', '0.01'));
        $this->assertSame(333, Money::unitsFrom('10.00', '0.03'));
        $this->assertSame(1, Money::unitsFrom('0.01', '0.01'));
    }

    public function test_money_comparison_ignores_trailing_zeros(): void
    {
        $this->assertSame(0, Money::compare('10', '10.00'));
        $this->assertSame(1, Money::compare('10.01', '10.00'));
    }
}
