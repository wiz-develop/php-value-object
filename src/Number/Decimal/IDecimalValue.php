<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

use BcMath\Number;
use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * 小数の値オブジェクト インターフェイス
 */
interface IDecimalValue
{
    public const MIN_VALUE = PHP_INT_MIN;
    public const MAX_VALUE = PHP_INT_MAX;

    /**
     * 小数点以下の桁数
     */
    public static function scale(): int;

    /**
     * 最小値
     * NOTE: 実装クラスでのオーバーライド用メソッド
     */
    public static function min(): Number;

    /**
     * 最大値
     * NOTE: 実装クラスでのオーバーライド用メソッド
     */
    public static function max(): Number;

    /**
     * 有効な範囲かどうか
     * NOTE: 実装クラスでのオーバーライド用メソッド
     * @return Result<bool,NumberValueError>
     */
    public static function isRangeValid(Number $value): Result;

    /**
     * 有効な値かどうか
     * NOTE: 実装クラスでのオーバーライド用メソッド
     * @return Result<bool,NumberValueError>
     */
    public static function isValid(Number $value): Result;

    /**
     * 信頼できるプリミティブ値からインスタンスを生成する
     */
    public static function from(Number $value): static;

    /**
     * 信頼できるプリミティブ値からインスタンスを生成する（Null許容）
     * @return Option<static>
     */
    public static function fromNullable(?Number $value): Option;

    /**
     * 信頼できないプリミティブ値からインスタンスを生成する
     * @return Result<static,NumberValueError>
     */
    public static function tryFrom(Number $value): Result;

    /**
     * 信頼できないプリミティブ値からインスタンスを生成する（Null許容）
     * @return Result<Option<static>,NumberValueError>
     */
    public static function tryFromNullable(?Number $value): Result;

    /**
     * 値を取得する
     */
    public function value(): Number;

    /**
     * 値がゼロかどうか
     */
    public function isZero(): bool;
}
