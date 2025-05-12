<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Error;

use BackedEnum;
use WizDevelop\PhpValueObject\Enum\IEnumValue;

/**
 * EnumValue エラー
 */
final readonly class EnumValueError
{
    /**
     * Enumの値が無効
     *
     * @template TBackedEnum of BackedEnum
     * @param class-string<IEnumValue> $className
     * @param TBackedEnum[]            $expectedValues
     */
    public static function invalidEnumValue(
        string $className,
        array $expectedValues,
        string|int $value,
    ): ValueObjectError {
        $displayName = ValueObjectError::getDisplayName($className);

        $expectedString = implode(
            separator: ', ',
            array: array_map(static fn ($e) => $e->value, $expectedValues),
        );

        return ValueObjectError::of(
            code: 'enum.invalidEnumValue',
            message: "{$displayName}の値は{$expectedString}のいずれかである必要があります。(値: {$value})",
        );
    }
}
