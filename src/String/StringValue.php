<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\String;

use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\String\Base\IStringValueFactory;
use WizDevelop\PhpValueObject\String\Base\StringValueBase;
use WizDevelop\PhpValueObject\String\Base\StringValueFactory;

/**
 * 文字列の値オブジェクトの性質を提供する
 */
readonly class StringValue extends StringValueBase implements IStringValueFactory
{
    use StringValueFactory;

    /**
     * Avoid new() operator.
     */
    final private function __construct(string $value)
    {
        parent::__construct($value);
    }

    #[Override]
    final public static function tryFrom(string $value): Result
    {
        return static::isValid($value)
            ->andThen(static fn () => self::isLengthValid($value))
            ->andThen(static fn () => self::isRegexValid($value))
            ->andThen(static fn () => Result\ok(static::from($value)));
    }

    #[Override]
    protected static function minLength(): int
    {
        return self::MIN_LENGTH;
    }

    #[Override]
    protected static function maxLength(): int
    {
        return self::MAX_LENGTH;
    }

    #[Override]
    protected static function regex(): string
    {
        return self::REGEX;
    }
}
