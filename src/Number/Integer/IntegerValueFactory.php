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
            // @phpstan-ignore-next-line
            return Result\ok(Option\none());
        }

        // @phpstan-ignore-next-line
        return static::tryFrom($value)->map(static fn ($result) => Option\some($result));
    }

    #[Override]
    final public static function tryFrom(int $value): Result
    {
        return static::isRangeValid($value)
            ->andThen(static fn () => static::isValid($value))
            ->andThen(static fn () => Result\ok(static::from($value)));
    }
}
