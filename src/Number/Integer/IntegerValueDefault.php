<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

use Override;
use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * Default implementation of IIntegerValue
 * @see WizDevelop\PhpValueObject\Number\Integer\IIntegerValue
 */
trait IntegerValueDefault
{
    #[Override]
    final public static function isRangeValid(int $value): Result
    {
        $minValue = max(static::min(), IIntegerValue::MIN_VALUE);
        $maxValue = min(static::max(), IIntegerValue::MAX_VALUE);

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
    public static function isValid(int $value): Result
    {
        return Result\ok(true);
    }

    #[Override]
    final public static function from(int $value): static
    {
        return new static($value);
    }

    #[Override]
    final public static function fromNullable(?int $value): Option
    {
        if ($value === null) {
            return Option\none();
        }

        return Option\some(static::from($value));
    }

    #[Override]
    final public static function tryFromNullable(?int $value): Result
    {
        if ($value === null) {
            // @phpstan-ignore-next-line
            return Result\ok(Option\none());
        }

        // @phpstan-ignore-next-line
        return static::tryFrom($value)->map(static fn ($result) => Option\some($result));
    }
}
