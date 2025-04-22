<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

use BCMath\Number;
use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\ValueObjectDefault;

/**
 * 小数の値オブジェクト
 */
abstract readonly class DecimalValue implements IDecimalValue, IArithmetic, IComparison
{
    use Arithmetic;
    use Comparison;
    use DecimalValueDefault;
    use ValueObjectDefault;

    /**
     * Avoid new() operator.
     */
    final private function __construct(private Number $value)
    {
        assert(static::min() <= static::max());
        assert(self::isRangeValid($value)->isOk());
        assert(self::isScaleValid($value)->isOk());
        assert(static::isValid($value)->isOk());
    }

    #[Override]
    public static function min(): Number
    {
        return new Number(IDecimalValue::MIN_VALUE);
    }

    #[Override]
    public static function max(): Number
    {
        return new Number(IDecimalValue::MAX_VALUE);
    }

    #[Override]
    final public static function tryFrom(Number $value): Result
    {
        return self::isRangeValid($value)
            ->andThen(static fn () => self::isScaleValid($value))
            ->andThen(static fn () => static::isValid($value))
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
