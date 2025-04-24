<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number;

use BcMath\Number;
use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\Decimal\DecimalValueBase;
use WizDevelop\PhpValueObject\Number\Decimal\DecimalValueFactory;
use WizDevelop\PhpValueObject\Number\Decimal\IDecimalValueFactory;

/**
 * 少数の値オブジェクト
 */
abstract readonly class DecimalValue extends DecimalValueBase implements IDecimalValueFactory
{
    use DecimalValueFactory;

    /**
     * Avoid new() operator.
     */
    final private function __construct(Number $value)
    {
        parent::__construct($value);
    }

    #[Override]
    protected static function min(): Number
    {
        return new Number(DecimalValueBase::MIN_VALUE);
    }

    #[Override]
    protected static function max(): Number
    {
        return new Number(DecimalValueBase::MAX_VALUE);
    }

    #[Override]
    final protected static function isRangeValid(Number $value): Result
    {
        $min = new Number(DecimalValueBase::MIN_VALUE);
        $max = new Number(DecimalValueBase::MAX_VALUE);
        $minValue = static::min() > $min ? static::min() : $min;
        $maxValue = static::max() < $max ? static::max() : $max;

        if ($value < $minValue || $value > $maxValue) {
            return Result\err(NumberValueError::invalidRange(
                className: static::class,
                min: $minValue,
                max: $maxValue,
                value: $value,
            ));
        }

        return Result\ok(true);
    }
}
