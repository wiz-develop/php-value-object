<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number;

use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\Integer\IIntegerValueFactory;
use WizDevelop\PhpValueObject\Number\Integer\IntegerValueBase;
use WizDevelop\PhpValueObject\Number\Integer\IntegerValueFactory;

/**
 * 負の整数の値オブジェクト
 */
abstract readonly class NegativeIntegerValue extends IntegerValueBase implements IIntegerValueFactory
{
    use IntegerValueFactory;

    /**
     * Avoid new() operator.
     */
    final private function __construct(int $value)
    {
        assert(static::max() < 0);
        parent::__construct($value);
    }

    #[Override]
    protected static function min(): int
    {
        return IntegerValueBase::MIN_VALUE;
    }

    #[Override]
    protected static function max(): int
    {
        return -1;
    }

    #[Override]
    final protected static function isRangeValid(int $value): Result
    {
        $minValue = max(static::min(), IntegerValueBase::MIN_VALUE);
        $maxValue = min(static::max(), 0);

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
