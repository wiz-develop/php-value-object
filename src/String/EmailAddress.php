<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\String;

use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\String\Base\StringValueBase;
use WizDevelop\PhpValueObject\String\Base\StringValueFactory;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * メールアドレスの値オブジェクト
 */
#[ValueObjectMeta(displayName: 'メールアドレス', description: 'メールアドレスの値オブジェクト')]
readonly class EmailAddress extends StringValueBase
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
        /** @var string */
        $sanitizedValue = filter_var($value, FILTER_SANITIZE_EMAIL);

        return self::isValid($sanitizedValue)
            ->andThen(static fn () => self::isValidLength($sanitizedValue))
            ->andThen(static fn () => self::isValidRegex($sanitizedValue))
            ->andThen(static fn () => self::isValidEmail($sanitizedValue))
            ->andThen(static fn () => Result\ok(self::from($sanitizedValue)));
    }

    /**
     * メールアドレスの最小文字数（RFC 5321に基づく）
     */
    #[Override]
    final public static function minLength(): int
    {
        return 1;
    }

    /**
     * メールアドレスの最大文字数（RFC 5321に基づく）
     */
    #[Override]
    final public static function maxLength(): int
    {
        return 254;
    }

    #[Override]
    final protected static function regex(): string
    {
        return self::REGEX;
    }

    /**
     * 有効な正規表現かどうか
     * @return Result<bool,ValueObjectError>
     */
    final protected static function isValidEmail(string $value): Result
    {
        $filteredValue = filter_var($value, FILTER_VALIDATE_EMAIL);
        if ($filteredValue === false) {
            return Result\err(ValueObjectError::string()->invalidEmail(
                className: static::class,
                value: $value,
            ));
        }

        return Result\ok(true);
    }
}
