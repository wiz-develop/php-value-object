<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * Default implementation of INegativeIntegerValue
 * @see WizDevelop\PhpValueObject\Number\Integer\INegativeIntegerValue
 */
trait NegativeIntegerValueDefault
{
    use IntegerValueDefault;

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
    public static function isNegative(int $value): Result
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
