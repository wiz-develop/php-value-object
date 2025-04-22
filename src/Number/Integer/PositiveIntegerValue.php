<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * 正の整数の値オブジェクト
 */
abstract readonly class PositiveIntegerValue extends IntegerValue implements IPositiveIntegerValue
{
    #[Override]
    public static function isZeroAllowed(): bool
    {
        return false;
    }

    #[Override]
    public static function min(): int
    {
        return static::isZeroAllowed() ? 0 : 1;
    }

    #[Override]
    final public static function isValid(int $value): Result
    {
        $min = static::isZeroAllowed() ? 0 : 1;

        if ($value < $min) {
            return Result\err(NumberValueError::invalidPositive(
                className: static::class,
                includeZero: static::isZeroAllowed(),
                value: $value,
            ));
        }

        return Result\ok(true);
    }
}
