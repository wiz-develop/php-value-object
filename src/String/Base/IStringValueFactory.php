<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\String\Base;

use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\String\StringValueError;

/**
 * 文字列の値オブジェクト ファクトリインターフェース
 * @see WizDevelop\PhpValueObject\String\Base\StringValueFactory
 */
interface IStringValueFactory
{
    /**
     * 信頼できるプリミティブ値からインスタンスを生成する
     */
    public static function from(string $value): static;

    /**
     * 信頼できるプリミティブ値からインスタンスを生成する（Null許容）
     * @return Option<static>
     */
    public static function fromNullable(?string $value): Option;

    /**
     * 信頼できないプリミティブ値からインスタンスを生成する
     * @return Result<static,StringValueError>
     */
    public static function tryFrom(string $value): Result;

    /**
     * 信頼できないプリミティブ値からインスタンスを生成する（Null許容）
     * @return Result<Option<static>,StringValueError>
     */
    public static function tryFromNullable(?string $value): Result;
}
