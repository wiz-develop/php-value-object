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
    public static function includeZero(): bool
    {
        return false;
    }

    #[Override]
    public static function min(): int
    {
        return static::includeZero() ? 0 : 1;
    }

    #[Override]
    final public static function isPositive(int $value): Result
    {
        $min = static::includeZero() ? 0 : 1;

        if ($value < $min) {
            return Result\err(NumberValueError::invalidPositive(
                className: static::class,
                includeZero: static::includeZero(),
                value: $value,
            ));
        }

        return Result\ok(true);
    }
}
