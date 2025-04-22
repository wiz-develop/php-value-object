<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\ValueObjectDefault;

/**
 * 整数の値オブジェクト
 */
abstract readonly class IntegerValue implements IIntegerValue, IArithmetic, IComparison
{
    use Arithmetic;
    use Comparison;
    use IntegerValueDefault;
    use ValueObjectDefault;

    /**
     * Avoid new() operator.
     */
    final private function __construct(private int $value)
    {
        assert(static::min() <= static::max());
        assert(self::isRangeValid($value)->isOk());
        assert(static::isValid($value)->isOk());
    }

    #[Override]
    public static function min(): int
    {
        return IIntegerValue::MIN_VALUE;
    }

    #[Override]
    public static function max(): int
    {
        return IIntegerValue::MAX_VALUE;
    }

    #[Override]
    final public static function tryFrom(int $value): Result
    {
        return self::isRangeValid($value)
            ->andThen(static fn () => static::isValid($value))
            ->andThen(static fn () => Result\ok(static::from($value)));
    }

    #[Override]
    final public function value(): int
    {
        return $this->value;
    }

    #[Override]
    final public function isZero(): bool
    {
        return $this->value === 0;
    }
}
