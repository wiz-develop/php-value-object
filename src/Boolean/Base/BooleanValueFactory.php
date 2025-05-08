<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Boolean\Base;

use Override;
use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;

/**
 * Default implementation of IBooleanValueFactory
 * @see WizDevelop\PhpValueObject\Boolean\BooleanValue
 * @see WizDevelop\PhpValueObject\Boolean\Base\IBooleanValueFactory
 */
trait BooleanValueFactory
{
    #[Override]
    final public static function from(bool $value): static
    {
        return new static($value);
    }

    #[Override]
    final public static function fromNullable(?bool $value): Option
    {
        if ($value === null) {
            return Option\none();
        }

        return Option\some(static::from($value));
    }

    #[Override]
    final public static function tryFromNullable(?bool $value): Result
    {
        if ($value === null) {
            // @phpstan-ignore return.type
            return Result\ok(Option\none());
        }

        // @phpstan-ignore return.type
        return static::tryFrom($value)->map(static fn ($result) => Option\some($result));
    }

    #[Override]
    final public static function true(): static
    {
        return static::from(true);
    }

    #[Override]
    final public static function false(): static
    {
        return static::from(false);
    }
}
