<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\String;

use WizDevelop\PhpValueObject\ValueObjectError;

/**
 * StringValue ドメインエラー
 */
final readonly class StringValueError extends ValueObjectError
{
    public static function invalid(
        string $message,
    ): static {
        return new self(
            code: __METHOD__,
            message: $message,
        );
    }

    /**
     * @param class-string $className
     */
    public static function invalidLength(
        string $className,
        int $min_length,
        int $max_length,
        string $value,
    ): static {
        $displayName = self::getDisplayName($className);

        return new static(
            code: __METHOD__,
            message: "{$displayName}は{$min_length}文字以上{$max_length}文字以下である必要があります。(値:{$value})",
        );
    }

    /**
     * @param class-string $className
     */
    public static function invalidRegex(
        string $className,
        string $regex,
        string $value,
    ): static {
        $displayName = self::getDisplayName($className);

        return new static(
            code: __METHOD__,
            message: "{$displayName}は正規表現({$regex})に一致する必要があります。(値:{$value})",
        );
    }
}
