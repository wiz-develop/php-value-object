<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * Default implementation of IPositiveIntegerValue
 * @see WizDevelop\PhpValueObject\Number\Integer\IPositiveIntegerValue
 */
trait PositiveIntegerValueDefault
{
    use IntegerValueDefault;

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
    public static function isPositive(int $value): Result
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
