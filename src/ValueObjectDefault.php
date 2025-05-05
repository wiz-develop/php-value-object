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
    final public function equals(IValueObject $other): bool
    {
        return (string)$this === (string)$other;
    }

    #[Override]
    final public function __toString(): string
    {
        return json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<mixed>
     */
    #[Override]
    final public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
