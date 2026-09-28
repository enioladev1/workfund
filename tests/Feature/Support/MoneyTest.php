<?php

use App\Support\Money;

test('converts a decimal amount to minor units', function () {
    expect(Money::toMinorUnits('499.99'))->toBe(49999);
    expect(Money::toMinorUnits('500'))->toBe(50000);
    expect(Money::toMinorUnits('0.01'))->toBe(1);
});

test('rejects an invalid decimal amount', function () {
    expect(fn () => Money::toMinorUnits('not-a-number'))->toThrow(InvalidArgumentException::class);
});

test('converts minor units back to a decimal string', function () {
    expect(Money::toDecimalString(49999))->toBe('499.99');
    expect(Money::toDecimalString(50000))->toBe('500.00');
    expect(Money::toDecimalString(1))->toBe('0.01');
});

test('formats an amount with its currency code', function () {
    expect(Money::format(49999, 'USD'))->toBe('USD 499.99');
});

test('computes a percentage of a minor-unit amount using bcmath', function () {
    expect(Money::percentageOf(10000, '10'))->toBe(1000);
    expect(Money::percentageOf(9999, '10'))->toBe(999);
});
