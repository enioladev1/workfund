<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * All money in this app is stored as integer minor units (cents). This class is the
 * only place decimal <-> minor-unit conversion happens, and it always uses bcmath,
 * never float arithmetic, per the project's money-handling rules.
 */
class Money
{
    private const MINOR_UNITS_EXPONENT = 2;

    /**
     * Convert a decimal string amount (e.g. "499.99") to integer minor units (49999).
     * Only used at input boundaries (e.g. parsing a configured dollar threshold).
     */
    public static function toMinorUnits(string $decimalAmount): int
    {
        if (! preg_match('/^-?\d+(\.\d{1,2})?$/', $decimalAmount) || ! is_numeric($decimalAmount)) {
            throw new InvalidArgumentException("Invalid decimal amount: {$decimalAmount}");
        }

        $scaled = bcmul($decimalAmount, bcpow('10', (string) self::MINOR_UNITS_EXPONENT));

        return (int) bcadd($scaled, '0', 0);
    }

    /**
     * Convert integer minor units to a decimal string for display, e.g. 49999 -> "499.99".
     * This is the only place rounding to a display value happens.
     */
    public static function toDecimalString(int $minorUnits): string
    {
        return bcdiv((string) $minorUnits, bcpow('10', (string) self::MINOR_UNITS_EXPONENT), self::MINOR_UNITS_EXPONENT);
    }

    public static function format(int $minorUnits, string $currency = 'USD'): string
    {
        return $currency.' '.self::toDecimalString($minorUnits);
    }

    /**
     * Percentage of a minor-unit amount using bcmath, e.g. 10% of 49999 cents.
     * $percent is a whole-number percentage (10 means 10%).
     */
    public static function percentageOf(int $minorUnits, string $percent): int
    {
        if (! is_numeric($percent)) {
            throw new InvalidArgumentException("Invalid percentage: {$percent}");
        }

        $result = bcdiv(bcmul((string) $minorUnits, $percent), '100', 0);

        return (int) $result;
    }
}
