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
 * 正の少数の値オブジェクト
 */
readonly class PositiveDecimalValue extends DecimalValueBase implements IDecimalValueFactory
{
    use DecimalValueFactory;

    /**
     * Avoid new() operator.
     */
    final private function __construct(Number $value)
    {
        assert(static::min() > new Number(0));
        parent::__construct($value);
    }

    #[Override]
    final public static function tryFrom(Number $value): Result
    {
        return static::isRangeValid($value)
            ->andThen(static fn () => static::isDigitsValid($value))
            ->andThen(static fn () => static::isValid($value))
            ->andThen(static fn () => Result\ok(static::from($value)));
    }

    #[Override]
    protected static function min(): Number
    {
        return new Number('0.0000000000000000000000000001');
    }

    #[Override]
    protected static function max(): Number
    {
        return new Number(DecimalValueBase::MAX_VALUE);
    }

    #[Override]
    final protected static function isRangeValid(Number $value): Result
    {
        $min = new Number(0);
        $max = new Number(DecimalValueBase::MAX_VALUE);
        $minValue = static::min() > $min ? static::min() : $min;
        $maxValue = static::max() < $max ? static::max() : $max;

        if ($value < $minValue || $value > $maxValue) {
            return Result\err(NumberValueError::invalidRange(
                className: static::class,
                min: $minValue,
                max: $maxValue,
                value: $value,
                isMinInclusive: false,
            ));
        }

        return Result\ok(true);
    }
}
