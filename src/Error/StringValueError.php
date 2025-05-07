<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Error;

use WizDevelop\PhpValueObject\String\Base\StringValueBase;

/**
 * StringValue エラー
 */
final readonly class StringValueError
{
    /**
     * 文字列の長さが無効
     * @param class-string<StringValueBase> $className
     */
    public static function invalidLength(
        string $className,
        int $min_length,
        int $max_length,
        string $value,
    ): ValueObjectError {
        $displayName = ValueObjectError::getDisplayName($className);

        return ValueObjectError::of(
            code: 'value_object.string.invalid_length',
            message: "{$displayName}は{$min_length}文字以上{$max_length}文字以下である必要があります。(値:{$value})",
        );
    }

    /**
     * 文字列の正規表現が無効
     * @param class-string<StringValueBase> $className
     */
    public static function invalidRegex(
        string $className,
        string $regex,
        string $value,
    ): ValueObjectError {
        $displayName = ValueObjectError::getDisplayName($className);

        return ValueObjectError::of(
            code: 'value_object.string.invalid_regex',
            message: "{$displayName}は正規表現({$regex})に一致する必要があります。(値:{$value})",
        );
    }

    /**
     * メールアドレスの形式が無効
     * @param class-string<StringValueBase> $className
     */
    public static function invalidEmail(
        string $className,
        string $value,
    ): ValueObjectError {
        $displayName = ValueObjectError::getDisplayName($className);

        return ValueObjectError::of(
            code: 'value_object.string.invalid_email',
            message: "{$displayName}は有効なメールアドレス形式である必要があります。(値:{$value})",
        );
    }

    /**
     * ULIDの形式が無効
     * @param class-string<StringValueBase> $className
     */
    public static function invalidUlid(
        string $className,
        string $value,
    ): ValueObjectError {
        $displayName = ValueObjectError::getDisplayName($className);

        return ValueObjectError::of(
            code: 'value_object.string.invalid_ulid',
            message: "{$displayName}は有効なULID形式である必要があります。(値:{$value})",
        );
    }
}
