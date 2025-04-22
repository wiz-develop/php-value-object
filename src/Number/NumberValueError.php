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
     * @param class-string $className
     */
    public static function invalidRange(
        string $className,
        Number|int|float $min,
        Number|int|float $max,
        Number|int|float $value,
    ): static {
        $displayName = self::getDisplayName($className);

        return new static(
            code: __METHOD__,
            message: "{$displayName}は{$min}以上{$max}以下である必要があります。(値:{$value})",
        );
    }

    /**
     * @param class-string $className
     */
    public static function invalidPositive(
        string $className,
        bool $includeZero,
        Number|int|float $value,
    ): static {
        $displayName = self::getDisplayName($className);
        $zeroText = $includeZero ? 'または0' : '';

        return new static(
            code: __METHOD__,
            message: "{$displayName}は正の数{$zeroText}である必要があります。(値:{$value})",
        );
    }

    /**
     * @param class-string $className
     */
    public static function invalidNegative(
        string $className,
        bool $includeZero,
        Number|int|float $value,
    ): static {
        $displayName = self::getDisplayName($className);
        $zeroText = $includeZero ? 'または0' : '';

        return new static(
            code: __METHOD__,
            message: "{$displayName}は負の数{$zeroText}である必要があります。(値:{$value})",
        );
    }

    /**
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
}
