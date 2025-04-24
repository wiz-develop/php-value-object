<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\IValueObject;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * 整数の値オブジェクトの基底クラス
 * @implements IValueObject<IntegerValueBase>
 */
abstract readonly class IntegerValueBase implements IValueObject, IArithmetic, IComparison
{
    use Arithmetic;
    use Comparison;

    protected const MIN_VALUE = PHP_INT_MIN;
    protected const MAX_VALUE = PHP_INT_MAX;

    protected function __construct(public int $value)
    {
        // NOTE: 不変条件（invariant）
        assert(static::min() <= static::max());
        assert(static::isRangeValid($value)->isOk());
        assert(static::isValid($value)->isOk());
    }

    /**
     * @param IntegerValueBase $other
     */
    #[Override]
    public function equals(IValueObject $other): bool
    {
        return (string)$this === (string)$other;
    }

    #[Override]
    public function __toString(): string
    {
        return (string)$this->value;
    }

    #[Override]
    public function jsonSerialize(): int
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
     * @return Result<bool,NumberValueError>
     */
    abstract protected static function isRangeValid(int $value): Result;

    /**
     * 有効な値かどうか
     * NOTE: 実装クラスでのオーバーライド用メソッド
     * @return Result<bool,NumberValueError>
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
}
