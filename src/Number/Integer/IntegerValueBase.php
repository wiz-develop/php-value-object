<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

use Override;
use Stringable;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\IValueObject;

/**
 * 整数の値オブジェクトの基底クラス
 */
abstract readonly class IntegerValueBase implements IValueObject, Stringable, IArithmetic, IComparison, IIntegerValueFactory
{
    use Arithmetic;
    use Comparison;

    final protected const MIN_VALUE = PHP_INT_MIN;
    final protected const MAX_VALUE = PHP_INT_MAX;

    protected function __construct(public int $value)
    {
        // NOTE: 不変条件（invariant）
        assert(static::min() <= static::max());
        assert(static::isValidRange($value)->isOk());
        assert(static::isValid($value)->isOk());
    }

    #[Override]
    final public function equals(IValueObject $other): bool
    {
        return $this->value === $other->value;
    }

    #[Override]
    final public function __toString(): string
    {
        return (string)$this->value;
    }

    #[Override]
    final public function jsonSerialize(): int
    {
        return $this->value;
    }

    /**
     * 最小値
     * NOTE: 実装クラスでのオーバーライド用メソッド
     */
    abstract protected static function min(): int;

    /**
     * 最大値
     * NOTE: 実装クラスでのオーバーライド用メソッド
     */
    abstract protected static function max(): int;

    /**
     * 有効な範囲かどうか
     * @return Result<bool,ValueObjectError>
     */
    abstract protected static function isValidRange(int $value): Result;

    /**
     * 有効な値かどうか
     * NOTE: 実装クラスでのオーバーライド用メソッド
     * @return Result<bool,ValueObjectError>
     */
    protected static function isValid(int $value): Result
    {
        return Result\ok(true);
    }

    /**
     * ゼロか
     */
    final public function isZero(): bool
    {
        return $this->value === 0;
    }

    /**
     * 正の値かどうか
     */
    final public function isPositive(): bool
    {
        return $this->value > 0;
    }

    /**
     * 負の値かどうか
     */
    final public function isNegative(): bool
    {
        return $this->value < 0;
    }
}
