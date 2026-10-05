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

    public function test_present_keeps_zeros_that_belong_to_the_whole_number(): void
    {
        $this->assertSame('10', Money::present('10'));
        $this->assertSame('10', Money::present('10.00'));
        $this->assertSame('100', Money::present('100.000000'));
        $this->assertSame('0.01', Money::present('0.010000'));
        $this->assertSame('10.5', Money::present('10.50'));
    }
}
