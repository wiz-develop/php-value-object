<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection\List;

use Closure;
use Override;
use WizDevelop\PhpValueObject\Collection\Base\CollectionDefault;
use WizDevelop\PhpValueObject\Collection\CollectionNotFoundException;
use WizDevelop\PhpValueObject\Collection\MultipleCollectionsFoundException;

/**
 * Default implementation of IListCollection
 * @see WizDevelop\PhpValueObject\Collection\List\IListCollection
 * @uses WizDevelop\PhpValueObject\Collection\ListCollection
 * @template TValue
 */
trait ListCollectionDefault
{
    /** @use CollectionDefault<int, TValue> */
    use CollectionDefault;

    /**
     * Create a new collection instance if the value isn't one already.
     *
     * @template TMakeValue
     *
     * @param  self<TMakeValue>|iterable<int,TMakeValue> $items
     * @return static<TMakeValue>
     */
    #[Override]
    final public static function make(self|iterable $items): static
    {
        if (is_array($items)) {
            return new static($items);
        }

        return new static(iterator_to_array($items));
    }

    #[Override]
    final public function last(?Closure $closure = null, $default = null)
    {
        if ($closure === null) {
            $elements = $this->elements;

            /** @var TValue */
            $element = end($elements);

            return key($elements) === null ? $default : $element;
        }

        foreach (array_reverse($this->elements) as $key => $value) {
            if ($closure($value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    #[Override]
    final public function lastOrFail(?Closure $closure = null)
    {
        $element = $this->last();

        if ($element !== null) {
            return $element;
        }

        throw new CollectionNotFoundException(static::class);
    }

    #[Override]
    final public function reverse(): static
    {
        return new static(array_reverse($this->elements));
    }

    #[Override]
    final public function first(?Closure $closure = null, $default = null)
    {
        if ($closure === null) {
            $elements = $this->elements;

            /** @var TValue */
            $element = reset($elements);

            return key($elements) === null ? $default : $element;
        }

        foreach ($this->elements as $key => $value) {
            if ($closure($value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    #[Override]
    final public function firstOrFail(?Closure $closure = null)
    {
        $element = $this->first($closure);

        if ($element !== null) {
            return $element;
        }

        throw new CollectionNotFoundException(static::class);
    }

    #[Override]
    final public function sole(?Closure $closure = null)
    {
        $items = $closure ? $this->filter($closure) : new static($this->elements);
        $count = $items->count();

        if ($count === 0) {
            throw new CollectionNotFoundException();
        }

        if ($count > 1) {
            throw new MultipleCollectionsFoundException($count);
        }

        return $items->firstOrFail($closure);
    }

    #[Override]
    final public function slice(int $offset, ?int $length = null): static
    {
        return new static(array_slice($this->elements, $offset, $length));
    }

    #[Override]
    final public function push(...$values): static
    {
        $elements = $this->elements;

        foreach ($values as $value) {
            $elements[] = $value;
        }

        return new static($elements);
    }

    #[Override]
    final public function concat(IListCollection $other): static
    {
        $elements = $this->elements;

        foreach ($other as $element) {
            $elements[] = $element;
        }

        return new static($elements);
    }

    #[Override]
    final public function merge(IListCollection $other): static
    {
        return new static(array_merge($this->elements, $other->toArray()));
    }

    /**
     * @template TMapValue
     *
     * @param  Closure(TValue,int): TMapValue $closure
     * @return self<TMapValue>
     */
    #[Override]
    final public function map(Closure $closure): self
    {
        $keys = array_keys($this->elements);

        return new self(array_map($closure, $this->elements, $keys));
    }

    #[Override]
    final public function mapStrict(Closure $closure): static
    {
        $keys = array_keys($this->elements);

        return new static(array_map($closure, $this->elements, $keys));
    }

    #[Override]
    final public function filter(Closure $closure): static
    {

        return new static(array_filter($this->elements, $closure, ARRAY_FILTER_USE_BOTH));
    }

    #[Override]
    final public function reject(Closure $closure): static
    {
        return $this->filter(static fn ($value, $key) => !$closure($value, $key));
    }

    #[Override]
    final public function unique(?Closure $closure = null): static
    {
        if ($closure === null) {
            return new static(array_unique($this->elements, SORT_REGULAR));
        }

        $exists = [];

        return $this->reject(static function ($value, $key) use ($closure, &$exists) {
            if (in_array($id = $closure($value, $key), $exists, true)) {
                return true;
            }

            $exists[] = $id;
        });
    }

    #[Override]
    final public function reduce(Closure $closure, $initial = null)
    {
        $result = $initial;

        foreach ($this->elements as $key => $value) {
            $result = $closure($result, $value, $key);
        }

        return $result;
    }

    #[Override]
    final public function contains($key): bool
    {
        if ($key instanceof Closure) {
            return $this->first($key) !== null;
        }

        return in_array($key, $this->elements, true);
    }

    #[Override]
    final public function every($key): bool
    {
        if ($key instanceof Closure) {
            foreach ($this->elements as $k => $v) {
                if (!$key($v, $k)) {
                    return false;
                }
            }

            return true;
        }

        $booleans = array_map(static fn ($e) => $e === $key, $this->elements);

        return (bool)array_product($booleans);
    }

    #[Override]
    final public function add($element): static
    {
        $elements = $this->elements;
        $elements[] = $element;

        return new static($elements);
    }

    #[Override]
    final public function sort(?Closure $closure = null): static
    {
        $elements = $this->elements;

        $closure ? uasort($elements, $closure) : asort($elements, SORT_REGULAR);

        return new static($elements);
    }
}
