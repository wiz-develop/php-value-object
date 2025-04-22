<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

use BcMath\Number;
use Override;
use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * Default implementation of IDecimalValue
 * @see WizDevelop\PhpValueObject\Number\Decimal\IDecimalValue
 */
trait DecimalValueDefault
{
    #[Override]
    public static function scale(): int
    {
        return 0;
    }

    #[Override]
    final public static function isRangeValid(Number $value): Result
    {
        $min = new Number(IDecimalValue::MIN_VALUE);
        $max = new Number(IDecimalValue::MAX_VALUE);
        $minValue = static::min() > $min ? static::min() : $min;
        $maxValue = static::max() < $max ? static::max() : $max;

        if ($value < $minValue || $value > $maxValue) {
            return Result\err(NumberValueError::invalidRange(
                className: static::class,
                min: $minValue,
                max: $maxValue,
                value: $value,
            ));
        }

        return Result\ok(true);
    }

    #[Override]
    final public static function isScaleValid(Number $value): Result
    {
        // スケールが設定値以内かチェック
        if ($value->scale > static::scale()) {
            return Result\err(NumberValueError::invalidScale(
                className: static::class,
                expectedScale: static::scale(),
                actualScale: $value->scale,
                value: $value
            ));
        }

        return Result\ok(true);
    }

    #[Override]
    public static function isValid(Number $value): Result
    {
        return Result\ok(true);
    }

    #[Override]
    final public static function from(Number $value): static
    {
        return new static($value);
    }

    #[Override]
    final public static function fromNullable(?Number $value): Option
    {
        if ($value === null) {
            return Option\none();
        }

        return Option\some(static::from($value));
    }

    #[Override]
    final public static function tryFromNullable(?Number $value): Result
    {
        if ($value === null) {
            // @phpstan-ignore-next-line
            return Result\ok(Option\none());
        }

        // @phpstan-ignore-next-line
        return static::tryFrom($value)->map(static fn ($result) => Option\some($result));
    }
}
