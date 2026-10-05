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
     * Drop fractional trailing zeros. Zeros that belong to the whole number stay,
     * so 10 and 10.00 both display as 10, and 0.010000 displays as 0.01.
     */
    public static function present(string $amount): string
    {
        $amount = trim($amount);

        if (! preg_match('/^\d+(\.\d+)?$/', $amount) || ! str_contains($amount, '.')) {
            return $amount;
        }

        $trimmed = rtrim(rtrim($amount, '0'), '.');

        return $trimmed === '' ? '0' : $trimmed;
    }

    public static function contribution(int $units, string $unitPrice): string
    {
        return bcmul((string) $units, self::normalize($unitPrice, 6), 2);
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
