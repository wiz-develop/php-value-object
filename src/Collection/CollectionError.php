<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection;

use WizDevelop\PhpValueObject\Collection\Base\CollectionBase;
use WizDevelop\PhpValueObject\ValueObjectError;

/**
 * Collection エラー
 * @extends ValueObjectError<CollectionBase<mixed,mixed>>
 */
final readonly class CollectionError extends ValueObjectError
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
     * 最低要素数が無効
     * @param class-string<CollectionBase<mixed,mixed>> $className
     */
    public static function invalidMinCount(
        string $className,
        int $min,
        int $count,
    ): static {
        $displayName = self::getDisplayName($className);

        return new static(
            code: __METHOD__,
            message: "{$displayName}は{$min}個以上である必要があります。(要素数:{$count})",
        );
    }

    /**
     * 最大要素数が無効
     * @param class-string<CollectionBase<mixed,mixed>> $className
     */
    public static function invalidMaxCount(
        string $className,
        int $max,
        int $count,
    ): static {
        $displayName = self::getDisplayName($className);

        return new static(
            code: __METHOD__,
            message: "{$displayName}は{$max}個以下である必要があります。(要素数:{$count})",
        );
    }

    /**
     * 要素数が無効
     * @param class-string<CollectionBase<mixed,mixed>> $className
     */
    public static function invalidRange(
        string $className,
        int $min,
        int $max,
        int $count,
    ): static {
        $displayName = self::getDisplayName($className);

        return new static(
            code: __METHOD__,
            message: "{$displayName}は{$min}個以上、{$max}個以下である必要があります。(要素数:{$count})",
        );
    }
}
