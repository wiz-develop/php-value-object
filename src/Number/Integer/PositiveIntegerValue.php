<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * 正の整数の値オブジェクト
 */
abstract readonly class PositiveIntegerValue extends IntegerValueBase implements IIntegerValueFactory
{
    use IntegerValueFactory;

    /**
     * Avoid new() operator.
     */
    final private function __construct(int $value)
    {
        assert(static::min() > 0);
        parent::__construct($value);
    }

    #[Override]
    protected static function min(): int
    {
        return 1;
    }

    #[Override]
    protected static function max(): int
    {
        return IntegerValueBase::MAX_VALUE;
    }

    #[Override]
    final protected static function isRangeValid(int $value): Result
    {
        $minValue = max(static::min(), 0);
        $maxValue = min(static::max(), IntegerValueBase::MAX_VALUE);

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

    #[Override]
    protected static function isValid(int $value): Result
    {
        return Result\ok(true);
    }
}
