<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\IValueObject;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * 整数の値オブジェクト インターフェイス
 */
interface IIntegerValue extends IValueObject
{
    public const MIN_VALUE = PHP_INT_MIN;
    public const MAX_VALUE = PHP_INT_MAX;

    /**
     * 最小値
     * NOTE: 実装クラスでのオーバーライド用メソッド
     */
    public static function min(): int;

    /**
     * 最大値
     * NOTE: 実装クラスでのオーバーライド用メソッド
     */
    public static function max(): int;

    /**
     * 有効な範囲かどうか
     * @return Result<bool,NumberValueError>
     */
    public static function isRangeValid(int $value): Result;

    /**
     * 有効な値かどうか
     * NOTE: 実装クラスでのオーバーライド用メソッド
     * @return Result<bool,NumberValueError>
     */
    public static function isValid(int $value): Result;

    /**
     * 信頼できるプリミティブ値からインスタンスを生成する
     */
    public static function from(int $value): static;

    /**
     * 信頼できるプリミティブ値からインスタンスを生成する（Null許容）
     * @return Option<static>
     */
    public static function fromNullable(?int $value): Option;

    /**
     * 信頼できないプリミティブ値からインスタンスを生成する
     * @return Result<static,NumberValueError>
     */
    public static function tryFrom(int $value): Result;

    /**
     * 信頼できないプリミティブ値からインスタンスを生成する（Null許容）
     * @return Result<Option<static>,NumberValueError>
     */
    public static function tryFromNullable(?int $value): Result;

    /**
     * 値を取得する
     */
    public function value(): int;

    /**
     * 値がゼロかどうか
     */
    public function isZero(): bool;
}
