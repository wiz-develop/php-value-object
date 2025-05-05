<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Boolean\Base;

use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Boolean\BooleanValueError;

/**
 * 真偽値の値オブジェクト ファクトリインターフェース
 * @see WizDevelop\PhpValueObject\Boolean\Base\BooleanValueFactory
 */
interface IBooleanValueFactory
{
    /**
     * 信頼できるプリミティブ値からインスタンスを生成する
     */
    public static function from(bool $value): static;

    /**
     * 信頼できるプリミティブ値からインスタンスを生成する（Null許容）
     * @return Option<static>
     */
    public static function fromNullable(?bool $value): Option;

    /**
     * 信頼できないプリミティブ値からインスタンスを生成する
     * @return Result<static,BooleanValueError>
     */
    public static function tryFrom(bool $value): Result;

    /**
     * 信頼できないプリミティブ値からインスタンスを生成する（Null許容）
     * @return Result<Option<static>,BooleanValueError>
     */
    public static function tryFromNullable(?bool $value): Result;

    /**
     * 真値のインスタンスを取得
     */
    public static function true(): static;

    /**
     * 偽値のインスタンスを取得
     */
    public static function false(): static;
}
