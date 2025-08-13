<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\String\Base;

use Override;
use Stringable;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\IValueObject;
use WizDevelop\PhpValueObject\Utils;

use function assert;

/**
 * 文字列の値オブジェクトの基底クラス
 */
abstract readonly class StringValueBase implements IValueObject, Stringable, IStringValueFactory
{
    final protected const int MIN_LENGTH = 1;
    final protected const int MAX_LENGTH = 4194303;
    final protected const string REGEX = '/^.*$/u';

    protected function __construct(public string $value)
    {
        // NOTE: 不変条件（invariant）
        assert(static::minLength() <= static::maxLength());
        Utils::assertResultIsOk(static::isValid($value));
        Utils::assertResultIsOk(static::isValidLength($value));
        Utils::assertResultIsOk(static::isValidRegex($value));
    }

    #[Override]
    final public function equals(IValueObject $other): bool
    {
        return $this->value === $other->value;
    }

    #[Override]
    final public function __toString(): string
    {
        return $this->value;
    }

    #[Override]
    final public function jsonSerialize(): string
    {
        return $this->value;
    }

    /**
     * 文字数の下限値
     * NOTE: 実装クラスでのオーバーライド用メソッド
     */
    abstract protected static function minLength(): int;

    /**
     * 文字数の上限値
     * NOTE: 実装クラスでのオーバーライド用メソッド
     */
    abstract protected static function maxLength(): int;

    /**
     * 文字列の正規表現
     * NOTE: 実装クラスでのオーバーライド用メソッド
     */
    abstract protected static function regex(): string;

    /**
     * 有効な文字列長かどうか
     * @return Result<bool,ValueObjectError>
     */
    final protected static function isValidLength(string $value): Result
    {
        $value_length = mb_strlen($value, 'UTF-8');
        $min_length = static::minLength() > self::MIN_LENGTH ? static::minLength() : self::MIN_LENGTH;
        $max_length = static::maxLength() < self::MAX_LENGTH ? static::maxLength() : self::MAX_LENGTH;

        if (!($value_length >= $min_length && $value_length <= $max_length)) {
            return Result\err(ValueObjectError::string()->invalidLength(
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
     * @return Result<bool,ValueObjectError>
     */
    final protected static function isValidRegex(string $value): Result
    {
        $regex = static::regex();
        $matchResult = preg_match($regex, $value);

        if ($regex !== self::REGEX && $matchResult !== 1) {
            return Result\err(ValueObjectError::string()->invalidRegex(
                className: static::class,
                regex: $regex,
                value: $value,
            ));
        }

        return Result\ok(true);
    }

    /**
     * 有効な値かどうか
     * NOTE: 実装クラスでのオーバーライド用メソッド
     * @return Result<bool,ValueObjectError>
     */
    protected static function isValid(string $value): Result
    {
        return Result\ok(true);
    }
}
