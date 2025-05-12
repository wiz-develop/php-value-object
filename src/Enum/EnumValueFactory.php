<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Enum;

use Override;
use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;

/**
 * Default implementation of IEnumValueFactory
 * @see WizDevelop\PhpValueObject\Enum\IEnumValueFactory
 */
trait EnumValueFactory
{
    #[Override]
    final public static function fromNullable(string|int|null $value): Option
    {
        if ($value === null) {
            return Option\none();
        }

        return Option\some(static::from($value));
    }

    #[Override]
    final public static function tryFrom2(string|int $value): Result
    {
        return static::isValid($value)
            ->andThen(static fn () => self::isValidEnumValue($value))
            ->andThen(static fn () => Result\ok(static::from($value)));
    }

    #[Override]
    final public static function tryFromNullable(string|int|null $value): Result
    {
        if ($value === null) {
            // @phpstan-ignore return.type
            return Result\ok(Option\none());
        }

        // @phpstan-ignore return.type
        return static::tryFrom2($value)->map(static fn ($result) => Option\some($result));
    }
}
