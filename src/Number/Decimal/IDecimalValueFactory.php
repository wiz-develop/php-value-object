<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

use BcMath\Number;
use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * すべての値オブジェクトの基底インターフェース
 * @see WizDevelop\PhpValueObject\Number\Decimal\DecimalValueFactory
 */
interface IDecimalValueFactory
{
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
}
