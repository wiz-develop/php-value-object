<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\String\Base;

use Override;
use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;

/**
 * Default implementation of IStringValueFactory
 * @see WizDevelop\PhpValueObject\String\StringValue
 * @see WizDevelop\PhpValueObject\String\Base\IStringValueFactory
 */
trait StringValueFactory
{
    #[Override]
    final public static function from(string $value): static
    {
        return new static($value);
    }

    #[Override]
    final public static function fromNullable(?string $value): Option
    {
        if ($value === null) {
            return Option\none();
        }

        return Option\some(static::from($value));
    }

    #[Override]
    final public static function tryFromNullable(?string $value): Result
    {
        if ($value === null) {
            // @phpstan-ignore-next-line
            return Result\ok(Option\none());
        }

        // @phpstan-ignore-next-line
        return static::tryFrom($value)->map(static fn ($result) => Option\some($result));
    }
}
