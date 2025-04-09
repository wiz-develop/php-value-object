<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject;

use Override;

/**
 * Default implementation of IValueObject
 * @see IValueObject
 */
trait ValueObjectDefault
{
    #[Override]
    public function equals(IValueObject $other): bool
    {
        return $other instanceof self && (string)$this === (string)$other;
    }

    #[Override]
    public function __toString(): string
    {
        return json_encode($this->jsonSerialize());
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
