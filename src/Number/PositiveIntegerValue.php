<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number;

use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\Number\Integer\IntegerValueBase;
use WizDevelop\PhpValueObject\Number\Integer\IntegerValueFactory;

/**
 * 正の整数の値オブジェクト
 */
readonly class PositiveIntegerValue extends IntegerValueBase
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
        return static::isValidRange($value)
            ->andThen(static fn () => static::isValid($value))
            ->andThen(static fn () => Result\ok(static::from($value)));
    }

    /**
     * @return positive-int
     */
    #[Override]
    protected static function min(): int
    {
        return 1;
    }

    /**
     * @return positive-int
     */
    #[Override]
    protected static function max(): int
    {
        return IntegerValueBase::MAX_VALUE;
    }

    #[Override]
    final protected static function isValidRange(int $value): Result
    {
        $minValue = max(static::min(), 0);
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

    #[Override]
    protected static function isValid(int $value): Result
    {
        return Result\ok(true);
    }
}
