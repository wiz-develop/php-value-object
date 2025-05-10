<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject;

use Override;

/**
 * 列挙型の値オブジェクトのデフォルト実装
 *
 * @template TValue of string|int
 */
trait EnumValueObjectDefault
{
    #[Override]
    final public function equals(IValueObject $other): bool
    {
        return $this === $other;
    }

    /**
     * @return TValue
     */
    #[Override]
    final public function jsonSerialize()
    {
        return $this->value;
    }
}
