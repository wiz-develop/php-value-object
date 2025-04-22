<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

use BcMath\Number;
use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * Default implementation of IDecimalPositiveValue
 * @see WizDevelop\PhpValueObject\Number\Decimal\IPositiveDecimalValue
 */
trait PositiveDecimalValueDefault
{
    #[Override]
    public static function includeZero(): bool
    {
        return false;
    }

    /**
     * 正の数かどうか
     * includeZeroがtrueの場合は0も許容する
     */
    #[Override]
    final private static function isPositive(Number $value): Result
    {
        $includeZero = static::includeZero();
        $compareResult = $value->compare(0);

        if ($compareResult < 0 || (!$includeZero && $compareResult === 0)) {
            return Result\err(NumberValueError::invalidPositive(
                className: static::class,
                includeZero: $includeZero,
                value: $value,
            ));
        }

        return Result\ok(true);
    }
}
