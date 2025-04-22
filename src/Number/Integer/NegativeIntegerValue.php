<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * 負の整数の値オブジェクト
 */
abstract readonly class NegativeIntegerValue extends IntegerValue implements INegativeIntegerValue
{
    #[Override]
    public static function isZeroAllowed(): bool
    {
        return false;
    }

    #[Override]
    public static function max(): int
    {
        return static::isZeroAllowed() ? 0 : -1;
    }

    #[Override]
    final public static function isValid(int $value): Result
    {
        $max = static::isZeroAllowed() ? 0 : -1;

        if ($value > $max) {
            return Result\err(NumberValueError::invalidNegative(
                className: static::class,
                includeZero: static::isZeroAllowed(),
                value: $value,
            ));
        }

        return Result\ok(true);
    }
}
