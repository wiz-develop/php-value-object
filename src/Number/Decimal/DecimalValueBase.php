<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

use BcMath\Number;
use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\IValueObject;

/**
 * 少数の値オブジェクトの基底クラス
 */
abstract readonly class DecimalValueBase implements IValueObject, IArithmetic, IComparison, IDecimalValueFactory
{
    use Arithmetic;
    use Comparison;

    final protected const string MIN_VALUE = '-9999999999999999999999999999.9';
    final protected const string MAX_VALUE = '9999999999999999999999999999.9';

    protected function __construct(public Number $value)
    {
        // NOTE: 不変条件（invariant）
        assert(static::min() <= static::max());
        // assert(static::min()->scale <= static::scale());
        // assert(static::max()->scale <= static::scale());
        assert(static::isValidRange($value)->isOk());
        assert(static::isValidDigits($value)->isOk());
        assert(static::isValid($value)->isOk());
    }

    #[Override]
    final public function equals(IValueObject $other): bool
    {
        return (string)$this === (string)$other;
    }

    #[Override]
    final public function __toString(): string
    {
        return (string)$this->value;
    }

    #[Override]
    final public function jsonSerialize(): string
    {
        return (string)$this;
    }

    /**
     * 小数点以下の桁数
     * NOTE: 実装クラスでのオーバーライド用メソッド
     * @return positive-int|0
     */
    protected static function scale(): int
    {
        return 0;
    }

    /**
     * 数値全体の有効桁数
     * NOTE: 実装クラスでのオーバーライド用メソッド
     * @return positive-int
     */
    protected static function precision(): int
    {
        return 29;
    }

    /**
     * 最小値
     * NOTE: 実装クラスでのオーバーライド用メソッド
     */
    abstract protected static function min(): Number;

    /**
     * 最大値
     * NOTE: 実装クラスでのオーバーライド用メソッド
     */
    abstract protected static function max(): Number;

    /**
     * 有効な範囲かどうか
     * @return Result<bool,ValueObjectError>
     */
    abstract protected static function isValidRange(Number $value): Result;

    /**
     * 有効な桁数かどうか
     * @return Result<bool,ValueObjectError>
     */
    final protected static function isValidDigits(Number $value): Result
    {
        $ret1 = str_replace('-', '', (string)$value); // マイナス記号を削除
        $ret2 = str_replace('.', '', $ret1); // 小数点を削除
        $didits = mb_strlen($ret2); // 文字列の長さが有効桁数

        if ($didits > static::precision()) {
            return Result\err(ValueObjectError::number()->invalidDigits(
                className: static::class,
                precision: static::precision(),
                actualDigits: $didits,
                value: $value
            ));
        }

        return Result\ok(true);
    }

    /**
     * 有効な値かどうか
     * NOTE: 実装クラスでのオーバーライド用メソッド
     * @return Result<bool,ValueObjectError>
     */
    protected static function isValid(Number $value): Result
    {
        return Result\ok(true);
    }

    /**
     * ゼロか
     */
    final public function isZero(): bool
    {
        return $this->value->compare(0) === 0;
    }

    /**
     * 小数点以下の桁数を指定してフォーマットする
     * @param positive-int|0|null $decimals 小数点以下の桁数
     */
    final public function format(?int $decimals = null): string
    {
        $d = $decimals ?? static::scale();

        return sprintf("%.{$d}f", (string)$this->value);
    }

    /**
     * 小数点以下の桁数を指定してフォーマットする(Number)
     * @param positive-int|0|null $decimals 小数点以下の桁数
     */
    final public function formatToNumber(?int $decimals = null): Number
    {
        return new Number(self::format($decimals));
    }
}
