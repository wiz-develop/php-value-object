<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

use BcMath\Number;
use Override;
use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;

/**
 * Default implementation of IDecimalValueFactory
 * @see WizDevelop\PhpValueObject\Number\Decimal\DecimalValueBase
 * @see WizDevelop\PhpValueObject\Number\Decimal\IDecimalValueFactory
 */
trait DecimalValueFactory
{
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
            // @phpstan-ignore return.type
            return Result\ok(Option\none());
        }

        // @phpstan-ignore return.type
        return static::tryFrom($value)->map(static fn ($result) => Option\some($result));
    }

    #[Override]
    public static function zero(): static
    {
        return static::from(new Number('0'));
    }
}
