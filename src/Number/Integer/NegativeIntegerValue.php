<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\ValueObjectDefault;

/**
 * 負の整数の値オブジェクト
 */
abstract readonly class NegativeIntegerValue implements INegativeIntegerValue, IArithmetic, IComparison
{
    use Arithmetic;
    use Comparison;
    use NegativeIntegerValueDefault;
    use ValueObjectDefault;

    /**
     * Avoid new() operator.
     */
    final private function __construct(private int $value)
    {
        assert(static::min() <= static::max());
        assert(static::includeZero() ? static::max() <= 0 : static::max() < 0);
        assert(self::isRangeValid($value)->isOk());
        assert(static::isValid($value)->isOk());
    }

    #[Override]
    final public static function tryFrom(int $value): Result
    {
        return self::isRangeValid($value)
            ->andThen(static fn () => static::isNegative($value))
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
