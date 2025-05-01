<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection\Base;

use Iterator;
use Override;

/**
 * コレクションイテレーター
 * @template TKey of array-key
 * @template TValue
 * @implements Iterator<TKey,TValue>
 */
final class CollectionIterator implements Iterator
{
    /**
     * @param array<TKey,TValue> $items
     */
    public function __construct(private array $items)
    {
    }

    /**
     * Rewind the Iterator to the first element.
     */
    #[Override]
    public function rewind(): void
    {
        $items = $this->getItems();
        reset($items);
    }

    /**
     * Return the current element.
     * @return TValue
     */
    #[Override]
    public function current(): mixed
    {
        $items = $this->getItems();

        // @phpstan-ignore-next-line
        return current($items);
    }

    /**
     * Return the key of the current element.
     *
     * @return TKey|null scalar on success, or null on failure
     */
    #[Override]
    public function key(): mixed
    {
        $items = $this->getItems();

        return key($items);
    }

    /**
     * Move forward to next element.
     */
    #[Override]
    public function next(): void
    {
        $items = $this->getItems();
        next($items);
    }

    /**
     * Checks if current position is valid.
     */
    #[Override]
    public function valid(): bool
    {
        $items = $this->getItems();

        return key($items) !== null;
    }

    /**
     * We need to return by reference since `next()` requires it.
     * @return array<TKey,TValue>
     */
    private function &getItems(): array
    {
        return $this->items;
    }
}
