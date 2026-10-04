<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/**
 * Decimal-string money helpers. Callers never pass floats into calculations.
 */
final class Money
{
    public static function normalize(string $amount, int $scale = 2): string
    {
        $amount = trim($amount);

        if (! preg_match('/^\d+(\.\d+)?$/', $amount)) {
            throw new InvalidArgumentException('Money amounts must be non-negative decimal strings.');
        }

        return bcadd($amount, '0', $scale);
    }

    public static function compare(string $left, string $right, int $scale = 2): int
    {
        return bccomp(self::normalize($left, $scale), self::normalize($right, $scale), $scale);
    }

    /**
     * Whole units purchased. bcdiv truncates toward zero, which floors a positive count
     * so a member is never credited a fraction of a unit.
     */
    public static function unitsFrom(string $amountUsd, string $unitPrice): int
    {
        $amount = self::normalize($amountUsd, 2);
        $price = self::normalize($unitPrice, 6);

        if (bccomp($price, '0', 6) !== 1) {
            throw new InvalidArgumentException('Unit price must be greater than zero.');
        }

        $units = bcdiv($amount, $price, 0);

        if ($units === null || ! preg_match('/^\d+$/', $units)) {
            throw new InvalidArgumentException('Could not calculate units for this payment.');
        }

        if (strlen($units) > 18 || bccomp($units, (string) PHP_INT_MAX, 0) === 1) {
            throw new InvalidArgumentException('This payment would credit more units than the ledger can store.');
        }

        return (int) $units;
    }
}
