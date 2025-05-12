<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Enum;

use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;

/**
 * 列挙型の値オブジェクト ファクトリインターフェース
 *
 * @see WizDevelop\PhpValueObject\Enum\EnumValueFactory
 */
interface IEnumValueFactory
{
    /**
     * 信頼できるプリミティブ値からインスタンスを生成する（Null許容）
     * @return Option<static>
     */
    public static function fromNullable(string|int|null $value): Option;

    /**
     * 信頼できないプリミティブ値からインスタンスを生成する
     * @return Result<static,ValueObjectError>
     */
    public static function tryFrom2(string|int $value): Result;

    /**
     * 信頼できないプリミティブ値からインスタンスを生成する（Null許容）
     * @return Result<Option<static>,ValueObjectError>
     */
    public static function tryFromNullable(string|int|null $value): Result;
}
