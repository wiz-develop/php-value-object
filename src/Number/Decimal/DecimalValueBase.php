<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

use BcMath\Number;
use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\IValueObject;
use WizDevelop\PhpValueObject\Number\NumberValueError;
use WizDevelop\PhpValueObject\ValueObjectDefault;

/**
 * 少数の値オブジェクトの抽象クラス
 */
abstract readonly class DecimalValueBase implements IValueObject, IArithmetic, IComparison, IDecimalValueFactory
{
    use Arithmetic;
    use Comparison;
    use ValueObjectDefault;

    protected const string MIN_VALUE = '-9999999999999999999999999999.9';
    protected const string MAX_VALUE = '9999999999999999999999999999.9';

    /**
     * Avoid new() operator.
     */
    protected function __construct(public Number $value)
    {
        assert(static::min() <= static::max());
        assert(static::isRangeValid($value)->isOk());
        assert(static::isScaleValid($value)->isOk());
        assert(static::isDigitsValid($value)->isOk());
        assert(static::isValid($value)->isOk());
    }

    #[Override]
    final public static function tryFrom(Number $value): Result
    {
        return static::isRangeValid($value)
            ->andThen(static fn () => static::isScaleValid($value))
            ->andThen(static fn () => static::isDigitsValid($value))
            ->andThen(static fn () => static::isValid($value))
            ->andThen(static fn () => Result\ok(static::from($value)));
    }

    /**
     * 小数点以下の桁数
     * NOTE: 実装クラスでのオーバーライド用メソッド
     */
    protected static function scale(): int
    {
        return 0;
    }

    /**
     * 有効最大桁数
     * NOTE: 実装クラスでのオーバーライド用メソッド
     */
    protected static function maxDigits(): int
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
     * @return Result<bool,NumberValueError>
     */
    abstract protected static function isRangeValid(Number $value): Result;

    /**
     * 有効なスケールかどうか
     * @return Result<bool,NumberValueError>
     */
    final protected static function isScaleValid(Number $value): Result
    {
        // スケールが設定値以内かチェック
        if ($value->scale > static::scale()) {
            return Result\err(NumberValueError::invalidScale(
                className: static::class,
                expectedScale: static::scale(),
                actualScale: $value->scale,
                value: $value
            ));
        }

        return Result\ok(true);
    }

    /**
     * 有効な桁数かどうか
     * @return Result<bool,NumberValueError>
     */
    final protected static function isDigitsValid(Number $value): Result
    {
        $ret1 = str_replace('-', '', (string)$value); // マイナス記号を削除
        $ret2 = str_replace('.', '', $ret1); // 小数点を削除
        $didits = mb_strlen($ret2); // 文字列の長さが有効桁数

        if ($didits > static::maxDigits()) {
            return Result\err(NumberValueError::invalidDigits(
                className: static::class,
                maxDigits: static::maxDigits(),
                actualDigits: $didits,
                value: $value
            ));
        }

        return Result\ok(true);
    }

    /**
     * 有効な値かどうか
     * NOTE: 実装クラスでのオーバーライド用メソッド
     * @return Result<bool,NumberValueError>
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
}
