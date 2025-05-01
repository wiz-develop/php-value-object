<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection\Base;

use Override;

/**
 * @template TKey of array-key
 * @template TValue
 * @see \IteratorAggregate
 */
trait IteratorAggregateDefault
{
    /**
     * @return CollectionIterator<TKey,TValue>
     */
    #[Override]
    public function getIterator(): CollectionIterator
    {
        return new CollectionIterator($this->elements);
    }
}
