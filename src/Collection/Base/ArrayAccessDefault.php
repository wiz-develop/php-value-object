<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection\Base;

use BadMethodCallException;
use Override;

/**
 * @template TKey of array-key
 * @template TValue
 * @see \ArrayAccess
 */
trait ArrayAccessDefault
{
    /**
     * Determine if an item exists at an offset.
     *
     * @param TKey $key
     */
    #[Override]
    public function offsetExists($key): bool
    {
        return isset($this->elements[$key]);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  TKey   $key
     * @return TValue
     */
    #[Override]
    public function offsetGet($key): mixed
    {
        return $this->elements[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  TKey|null              $key
     * @param  TValue                 $value
     * @throws BadMethodCallException
     */
    #[Override]
    public function offsetSet($key, $value): void
    {
        throw new BadMethodCallException('Collection does not support offsetSet due to its immutable.');
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  TKey                   $key
     * @throws BadMethodCallException
     */
    #[Override]
    public function offsetUnset($key): void
    {
        throw new BadMethodCallException('Collection does not support offsetUnset due to its immutable.');
    }
}
