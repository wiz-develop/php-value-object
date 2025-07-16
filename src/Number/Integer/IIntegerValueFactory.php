<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;

/**
 * 整数の値オブジェクト ファクトリインターフェース
 * @see WizDevelop\PhpValueObject\Number\Integer\IntegerValueFactory
 */
interface IIntegerValueFactory
{
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
     * @return Result<static,ValueObjectError>
     */
    public static function tryFrom(int $value): Result;

    /**
     * 信頼できないプリミティブ値からインスタンスを生成する（Null許容）
     * @return Result<Option<static>,ValueObjectError>
     */
    public static function tryFromNullable(?int $value): Result;

    /**
     * ゼロの値オブジェクトを生成する
     */
    public static function zero(): static;
}
