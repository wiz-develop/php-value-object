<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Enum;

use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\IValueObject;

/**
 * 列挙型の値オブジェクトのデフォルト実装
 */
trait EnumValueObjectDefault
{
    #[Override]
    public function equals(IValueObject $other): bool
    {
        return $this === $other;
    }

    #[Override]
    public function jsonSerialize(): string|int
    {
        return $this->value;
    }

    /**
     * 有効な値かどうか
     * NOTE: 実装クラスでのオーバーライド用メソッド
     *
     * @return Result<bool,ValueObjectError>
     */
    private static function isValidEnumValue(string|int $value): Result
    {
        $tryFromResult = static::tryFrom($value);

        if ($tryFromResult === null) {
            /**
             * @var static[]
             * @phpstan-ignore-next-line
             */
            $expectedValues = static::cases();

            return Result\err(ValueObjectError::enum()->invalidEnumValue(
                className: static::class,
                expectedValues: $expectedValues,
                value: $value,
            ));
        }

        return Result\ok();
    }

    /**
     * 有効な値かどうか
     * NOTE: 実装クラスでのオーバーライド用メソッド
     * @return Result<bool,ValueObjectError>
     */
    protected static function isValid(string|int $value): Result
    {
        return Result\ok(true);
    }
}
