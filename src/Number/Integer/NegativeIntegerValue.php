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
    public static function includeZero(): bool
    {
        return false;
    }

    #[Override]
    public static function max(): int
    {
        return static::includeZero() ? 0 : -1;
    }

    #[Override]
    final public static function isNegative(int $value): Result
    {
        $max = static::includeZero() ? 0 : -1;

        if ($value > $max) {
            return Result\err(NumberValueError::invalidNegative(
                className: static::class,
                includeZero: static::includeZero(),
                value: $value,
            ));
        }

        return Result\ok(true);
    }
}
