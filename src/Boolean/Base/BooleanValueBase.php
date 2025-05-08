<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Boolean\Base;

use Override;
use Stringable;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\IValueObject;

/**
 * 真偽値の値オブジェクトの基底クラス
 */
abstract readonly class BooleanValueBase implements IValueObject, Stringable, IBooleanValueFactory
{
    protected function __construct(public bool $value)
    {
        assert(static::isValid($value)->isOk());
    }

    #[Override]
    final public function equals(IValueObject $other): bool
    {
        return $this->value === $other->value;
    }

    #[Override]
    final public function __toString(): string
    {
        return $this->value ? 'true' : 'false';
    }

    #[Override]
    final public function jsonSerialize(): bool
    {
        return $this->value;
    }

    /**
     * 有効な値かどうか
     * NOTE: 実装クラスでのオーバーライド用メソッド
     * @return Result<bool,ValueObjectError>
     */
    protected static function isValid(bool $value): Result
    {
        return Result\ok(true);
    }

    /**
     * 真の値かどうか
     */
    final public function yes(): bool
    {
        return $this->value === true;
    }

    /**
     * 偽の値かどうか
     */
    final public function no(): bool
    {
        return $this->value === false;
    }

    /**
     * 否定値を取得
     */
    final public function not(): static
    {
        return static::from(!$this->value);
    }

    /**
     * 論理積
     */
    final public function and(self $other): static
    {
        return static::from($this->value && $other->value);
    }

    /**
     * 論理和
     */
    final public function or(self $other): static
    {
        return static::from($this->value || $other->value);
    }

    /**
     * 排他的論理和
     */
    final public function xor(self $other): static
    {
        return static::from($this->value xor $other->value);
    }
}
