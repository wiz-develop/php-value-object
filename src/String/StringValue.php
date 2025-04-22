<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\String;

use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\ValueObjectDefault;

use function assert;

/**
 * 文字列の値オブジェクトの性質を提供する
 */
abstract readonly class StringValue implements IStringValue
{
    use StringValueDefault;
    use ValueObjectDefault;

    /**
     * Avoid new() operator.
     * @see from()
     * @see tryFrom()
     */
    final private function __construct(public string $value)
    {
        assert(static::minLength() <= static::maxLength());
        assert(static::isValid($value)->isOk());
        assert(self::isLengthValid($value)->isOk());
        assert(self::isRegexValid($value)->isOk());
    }

    /**
     * 信頼できるプリミティブ値からインスタンスを生成する
     */
    final public static function from(string $value): static
    {
        return new static($value);
    }

    /**
     * 信頼できるプリミティブ値からインスタンスを生成する（Null許容）
     * @return Option<static>
     */
    final public static function fromNullable(?string $value): Option
    {
        if ($value === null) {
            return Option\none();
        }

        return Option\some(static::from($value));
    }

    /**
     * 信頼できないプリミティブ値からインスタンスを生成する
     * @return Result<static,StringValueError>
     */
    final public static function tryFrom(string $value): Result
    {
        return static::isValid($value)
            ->andThen(static fn () => self::isLengthValid($value))
            ->andThen(static fn () => self::isRegexValid($value))
            ->andThen(static fn () => Result\ok(static::from($value)));
    }

    /**
     * 信頼できないプリミティブ値からインスタンスを生成する（Null許容）
     * @return Result<Option<static>,StringValueError>
     */
    final public static function tryFromNullable(?string $value): Result
    {
        if ($value === null) {
            // @phpstan-ignore-next-line
            return Result\ok(Option\none());
        }

        // @phpstan-ignore-next-line
        return static::tryFrom($value)->map(static fn ($result) => Option\some($result));
    }

    /**
     * 有効な文字列長かどうか
     * @return Result<bool,StringValueError>
     */
    final public static function isLengthValid(string $value): Result
    {
        $value_length = mb_strlen($value, 'UTF-8');
        $min_length = static::minLength() > self::MIN_LENGTH ? static::minLength() : self::MIN_LENGTH;
        $max_length = static::maxLength() < self::MAX_LENGTH ? static::maxLength() : self::MAX_LENGTH;

        if (!($value_length >= $min_length && $value_length <= $max_length)) {
            return Result\err(StringValueError::invalidLength(
                className: static::class,
                min_length: $min_length,
                max_length: $max_length,
                value: $value,
            ));
        }

        return Result\ok(true);
    }

    /**
     * 有効な正規表現かどうか
     * @return Result<bool,StringValueError>
     */
    final public static function isRegexValid(string $value): Result
    {
        $regex = static::regex();

        if ($regex !== self::REGEX && !preg_match($regex, $value)) {
            return Result\err(StringValueError::invalidRegex(
                className: static::class,
                regex: $regex,
                value: $value,
            ));
        }

        return Result\ok(true);
    }
}
