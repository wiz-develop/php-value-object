<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number;

use BcMath\Number;
use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\Number\Decimal\DecimalValueBase;
use WizDevelop\PhpValueObject\Number\Decimal\DecimalValueFactory;

/**
 * 負の少数の値オブジェクト
 */
readonly class NegativeDecimalValue extends DecimalValueBase
{
    use DecimalValueFactory;

    /**
     * Avoid new() operator.
     */
    final private function __construct(Number $value)
    {
        assert(static::max() < new Number(0));
        parent::__construct($value);
    }

    #[Override]
    final public static function tryFrom(Number $value): Result
    {
        return static::isValidRange($value)
            ->andThen(static fn () => static::isValidDigits($value))
            ->andThen(static fn () => static::isValid($value))
            ->andThen(static fn () => Result\ok(static::from($value)));
    }

    #[Override]
    protected static function min(): Number
    {
        return new Number(DecimalValueBase::MIN_VALUE);
    }

    #[Override]
    protected static function max(): Number
    {
        return  new Number('-0.0000000000000000000000000001');
    }

    #[Override]
    final protected static function isValidRange(Number $value): Result
    {
        $min = new Number(DecimalValueBase::MIN_VALUE);
        $max = new Number(0);
        $minValue = static::min() > $min ? static::min() : $min;
        $maxValue = static::max() < $max ? static::max() : $max;

        if ($value < $minValue || $value > $maxValue) {
            return Result\err(ValueObjectError::number()->invalidRange(
                className: static::class,
                min: $minValue,
                max: $maxValue,
                value: $value,
                isMaxInclusive: false,
            ));
        }

        return Result\ok(true);
    }
}
