<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number;

use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\Number\Integer\IntegerValueBase;
use WizDevelop\PhpValueObject\Number\Integer\IntegerValueFactory;

/**
 * 整数の値オブジェクト
 */
readonly class IntegerValue extends IntegerValueBase
{
    use IntegerValueFactory;

    /**
     * Avoid new() operator.
     */
    final private function __construct(int $value)
    {
        parent::__construct($value);
    }

    #[Override]
    final public static function tryFrom(int $value): Result
    {
        return static::isRangeValid($value)
            ->andThen(static fn () => static::isValid($value))
            ->andThen(static fn () => Result\ok(static::from($value)));
    }

    #[Override]
    protected static function min(): int
    {
        return IntegerValueBase::MIN_VALUE;
    }

    #[Override]
    protected static function max(): int
    {
        return IntegerValueBase::MAX_VALUE;
    }

    #[Override]
    final protected static function isRangeValid(int $value): Result
    {
        $minValue = max(static::min(), IntegerValueBase::MIN_VALUE);
        $maxValue = min(static::max(), IntegerValueBase::MAX_VALUE);

        if ($value < $minValue || $value > $maxValue) {
            return Result\err(ValueObjectError::number()->invalidRange(
                className: static::class,
                min: $minValue,
                max: $maxValue,
                value: $value,
            ));
        }

        return Result\ok(true);
    }
}
