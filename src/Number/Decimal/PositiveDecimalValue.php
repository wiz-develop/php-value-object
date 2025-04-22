<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

use BcMath\Number;
use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\ValueObjectDefault;

/**
 * 正の小数の値オブジェクト
 */
abstract readonly class PositiveDecimalValue implements IDecimalValue, IPositiveDecimalValue, IArithmetic, IComparison
{
    use Arithmetic;
    use Comparison;
    use DecimalValueDefault;
    use PositiveDecimalValueDefault;
    use ValueObjectDefault;

    /**
     * Avoid new() operator.
     */
    final private function __construct(private Number $value)
    {
        assert(static::min() <= static::max());
        assert(static::includeZero() ? static::min() >= new Number(0) : static::min() > new Number(0));
        assert(static::isRangeValid($value)->isOk());
        assert(static::isScaleValid($value)->isOk());
        assert(static::isPositive($value)->isOk());
        assert(static::isValid($value)->isOk());
    }

    #[Override]
    public static function min(): Number
    {
        return new Number(0);
    }

    #[Override]
    public static function max(): Number
    {
        return new Number(IDecimalValue::MAX_VALUE);
    }

    #[Override]
    final public static function tryFrom(Number $value): Result
    {
        return static::isValid($value)
            ->andThen(static fn () => static::isRangeValid($value))
            ->andThen(static fn () => static::isScaleValid($value))
            ->andThen(static fn () => static::isPositive($value))
            ->andThen(static fn () => Result\ok(static::from($value)));
    }

    #[Override]
    final public function value(): Number
    {
        return $this->value;
    }

    #[Override]
    final public function isZero(): bool
    {
        return $this->value->compare(0) === 0;
    }
}
