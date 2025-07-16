<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

use Override;
use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;

/**
 * Default implementation of IIntegerValueFactory
 * @see WizDevelop\PhpValueObject\Number\Integer\IntegerValueBase
 * @see WizDevelop\PhpValueObject\Number\Integer\IIntegerValueFactory
 */
trait IntegerValueFactory
{
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
            // @phpstan-ignore return.type
            return Result\ok(Option\none());
        }

        // @phpstan-ignore return.type
        return static::tryFrom($value)->map(static fn ($result) => Option\some($result));
    }

    #[Override]
    public static function zero(): static
    {
        return static::from(0);
    }
}
