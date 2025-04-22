<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

use BcMath\Number;
use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\ValueObjectDefault;

/**
 * 負の小数の値オブジェクト
 */
abstract readonly class NegativeDecimalValue implements IDecimalValue, INegativeDecimalValue, IArithmetic, IComparison
{
    use Arithmetic;
    use Comparison;
    use DecimalValueDefault;
    use NegativeDecimalValueDefault;
    use ValueObjectDefault;

    /**
     * Avoid new() operator.
     */
    final private function __construct(private Number $value)
    {
        assert(self::min() <= self::max());
        assert(self::isRangeValid($value)->isOk());
        assert(self::isScaleValid($value)->isOk());
        assert(self::isNegative($value)->isOk());
        assert(self::isValid($value)->isOk());
    }

    #[Override]
    final public static function tryFrom(Number $value): Result
    {
        return static::isValid($value)
            ->andThen(static fn () => static::isRangeValid($value))
            ->andThen(static fn () => static::isScaleValid($value))
            ->andThen(static fn () => static::isNegative($value))
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
