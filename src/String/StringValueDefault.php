<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\String;

use Override;
use WizDevelop\PhpMonad\Result;

/**
 * Default implementation of StringValue
 * @see StringValue
 */
trait StringValueDefault
{
    #[Override]
    public static function minLength(): int
    {
        return IStringValue::MIN_LENGTH;
    }

    #[Override]
    public static function maxLength(): int
    {
        return IStringValue::MAX_LENGTH;
    }

    #[Override]
    public static function regex(): string
    {
        return IStringValue::REGEX;
    }

    #[Override]
    public static function isValid(string $value): Result
    {
        return Result\ok(true);
    }
}
