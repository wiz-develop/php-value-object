<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\String;

use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\IValueObject;

/**
 * 文字列の値オブジェクト インターフェイス
 */
interface IStringValue extends IValueObject
{
    public const int MIN_LENGTH = 1;
    public const int MAX_LENGTH = 4194303;
    public const string REGEX = '/^.*$/u';

    /**
     * 文字数の下限値
     * NOTE: 実装クラスでのオーバーライド用メソッド
     */
    public static function minLength(): int;

    /**
     * 文字数の上限値
     * NOTE: 実装クラスでのオーバーライド用メソッド
     */
    public static function maxLength(): int;

    /**
     * 文字列の正規表現
     * NOTE: 実装クラスでのオーバーライド用メソッド
     */
    public static function regex(): string;

    /**
     * 有効な値かどうか
     * NOTE: 実装クラスでのオーバーライド用メソッド
     * @return Result<bool,StringValueError>
     */
    public static function isValid(string $value): Result;
}
