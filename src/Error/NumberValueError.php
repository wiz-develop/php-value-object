<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Error;

use BcMath\Number;

/**
 * NumberValue エラー
 */
final readonly class NumberValueError
{
    /**
     * 数値の範囲が無効
     * @param class-string<NumberValueBase> $className
     */
    public static function invalidRange(
        string $className,
        Number|int $min,
        Number|int $max,
        Number|int $value,
        bool $isMinInclusive = true,
        bool $isMaxInclusive = true,
    ): ValueObjectError {
        $displayName = ValueObjectError::getDisplayName($className);

        if ($min === $max) {
            return ValueObjectError::of(
                code: 'value_object.number.invalid_range_exact',
                message: "{$displayName}は{$min}である必要があります。(値:{$value})",
            );
        }

        $minText = $isMinInclusive ? '以上' : 'より大きい';
        $maxText = $isMaxInclusive ? '以下' : '未満';

        return ValueObjectError::of(
            code: 'value_object.number.invalid_range',
            message: "{$displayName}は{$min}{$minText}かつ{$max}{$maxText}である必要があります。(値:{$value})",
        );
    }

    /**
     * ゼロによる除算は無効
     * @param class-string<NumberValueBase> $className
     */
    public static function invalidDivideByZero(
        string $className,
    ): ValueObjectError {
        $displayName = ValueObjectError::getDisplayName($className);

        return ValueObjectError::of(
            code: 'value_object.number.invalid_divide_by_zero',
            message: "{$displayName}はゼロによる除算ができません。",
        );
    }

    /**
     * 数値の桁数が無効
     * @param class-string<NumberValueBase> $className
     */
    public static function invalidDigits(
        string $className,
        int $precision,
        int $actualDigits,
        Number|int $value,
    ): ValueObjectError {
        $displayName = ValueObjectError::getDisplayName($className);

        return ValueObjectError::of(
            code: 'value_object.number.invalid_digits',
            message: "{$displayName}は桁数{$precision}桁まで許容されますが、{$actualDigits}桁の値が指定されました。(値:{$value})",
        );
    }
}
