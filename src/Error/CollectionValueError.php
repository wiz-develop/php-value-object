<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Error;

use WizDevelop\PhpValueObject\Collection\Base\CollectionBase;

/**
 * CollectionValue エラー
 */
final readonly class CollectionValueError
{
    /**
     * 最低要素数が無効
     * @param class-string<CollectionBase<mixed,mixed>> $className
     */
    public static function invalidMinCount(
        string $className,
        int $min,
        int $count,
    ): ValueObjectError {
        $displayName = ValueObjectError::getDisplayName($className);

        return ValueObjectError::of(
            code: 'value_object.collection.invalid_min_count',
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
    ): ValueObjectError {
        $displayName = ValueObjectError::getDisplayName($className);

        return ValueObjectError::of(
            code: 'value_object.collection.invalid_max_count',
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
    ): ValueObjectError {
        $displayName = ValueObjectError::getDisplayName($className);

        return ValueObjectError::of(
            code: 'value_object.collection.invalid_range',
            message: "{$displayName}は{$min}個以上、{$max}個以下である必要があります。(要素数:{$count})",
        );
    }

    /**
     * 要素が無効
     * @param class-string<CollectionBase<mixed,mixed>> $className
     */
    public static function invalidElementValues(string $className, IErrorValue ...$errors): ValueObjectError
    {
        $displayName = ValueObjectError::getDisplayName($className);

        $errorsStr = implode(
            ', ',
            array_map(static fn (IErrorValue $error) => $error->getMessage(), $errors),
        );

        return ValueObjectError::of(
            code: 'value_object.collection.invalid_element_values',
            message: "{$displayName}に無効な要素が含まれています。(無効な要素の詳細: {$errorsStr})",
        );
    }
}
