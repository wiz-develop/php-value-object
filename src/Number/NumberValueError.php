<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number;

use BcMath\Number;
use WizDevelop\PhpValueObject\ValueObjectError;

/**
 * NumberValue エラー
 */
final readonly class NumberValueError extends ValueObjectError
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
     * 数値の範囲が無効
     * @param class-string $className
     */
    public static function invalidRange(
        string $className,
        Number|int $min,
        Number|int $max,
        Number|int $value,
        bool $isMinInclusive = true,
        bool $isMaxInclusive = true,
    ): static {
        $displayName = self::getDisplayName($className);
        $minText = $isMinInclusive ? '以上' : 'より大きい';
        $maxText = $isMaxInclusive ? '以下' : '未満';

        return new static(
            code: __METHOD__,
            message: "{$displayName}は{$min}{$minText}かつ{$max}{$maxText}である必要があります。(値:{$value})",
        );
    }

    /**
     * ゼロによる除算は無効
     * @param class-string $className
     */
    public static function invalidDivideByZero(
        string $className,
    ): static {
        $displayName = self::getDisplayName($className);

        return new static(
            code: __METHOD__,
            message: "{$displayName}はゼロによる除算ができません。",
        );
    }

    /**
     * 数値のスケールが無効
     * @param class-string $className
     */
    public static function invalidScale(
        string $className,
        int $expectedScale,
        int $actualScale,
        Number|int $value,
    ): static {
        $displayName = self::getDisplayName($className);

        return new static(
            code: __METHOD__,
            message: "{$displayName}は小数点以下{$expectedScale}桁まで許容されますが、{$actualScale}桁の値が指定されました。(値:{$value})",
        );
    }

    /**
     * 数値の桁数が無効
     * @param class-string $className
     */
    public static function invalidDigits(
        string $className,
        int $maxDigits,
        int $actualDigits,
        Number|int $value,
    ): static {
        $displayName = self::getDisplayName($className);

        return new static(
            code: __METHOD__,
            message: "{$displayName}は桁数{$maxDigits}桁まで許容されますが、{$actualDigits}桁の値が指定されました。(値:{$value})",
        );
    }
}
