<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

use BcMath\Number;
use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * Default implementation of IDecimalNegativeValue
 * @see WizDevelop\PhpValueObject\Number\Decimal\INegativeDecimalValue
 */
trait NegativeDecimalValueDefault
{
    #[Override]
    public static function includeZero(): bool
    {
        return false;
    }

    #[Override]
    final private static function isNegative(Number $value): Result
    {
        $includeZero = static::includeZero();
        $compareResult = $value->compare(0);

        if ($compareResult > 0 || (!$includeZero && $compareResult === 0)) {
            return Result\err(NumberValueError::invalidNegative(
                className: static::class,
                includeZero: $includeZero,
                value: $value,
            ));
        }

        return Result\ok(true);
    }
}
