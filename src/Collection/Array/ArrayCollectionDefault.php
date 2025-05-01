<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection\Array;

use Closure;
use Override;
use WizDevelop\PhpValueObject\Collection\Base\ICollection;

/**
 * Default implementation of ICollection
 * @see WizDevelop\PhpValueObject\Collection\Array\IArrayCollection
 * @uses WizDevelop\PhpValueObject\Collection\ArrayCollection
 * @template TKey of array-key
 * @template TValue
 */
trait ArrayCollectionDefault
{
    /**
     * Create a new collection instance if the value isn't one already.
     *
     * @template TMakeKey of array-key
     * @template TMakeValue
     *
     * @param  self<TMakeKey, TMakeValue>|iterable<TMakeKey, TMakeValue> $items
     * @return static<TMakeKey, TMakeValue>
     */
    #[Override]
    final public static function make(self|iterable $items): static
    {
        if (is_array($items)) {
            return new static($items);
        }

        return new static(iterator_to_array($items));
    }

    /**
     * Map a collection and flatten the result by a single level.
     *
     * @template TFlatMapKey of array-key
     * @template TFlatMapValue
     *
     * @param  Closure(TValue, TKey): (self<TFlatMapKey, TFlatMapValue>|array<TFlatMapKey, TFlatMapValue>) $closure
     * @return self<TFlatMapKey,TFlatMapValue>
     */
    #[Override]
    final public function flatMap(Closure $closure): self
    {
        // @phpstan-ignore-next-line
        return $this->map($closure)->collapse();
    }

    /**
     * Collapse the collection of items into a single array.
     * @return self<TKey,mixed>
     */
    #[Override]
    final public function collapse(): self
    {
        $elements = [];

        foreach ($this->elements as $element) {
            if ($element instanceof ICollection) {
                $element = $element->toArray();
            } elseif (!is_array($element)) {
                continue;
            }

            $elements[] = $element;
        }

        return new self(...$elements);
    }

    /**
     * Run a dictionary map over the items.
     *
     * The closure should return an associative array with a single key/value pair.
     *
     * @template TMapToDictionaryKey of array-key
     * @template TMapToDictionaryValue
     *
     * @param  Closure(TValue,TKey): array<TMapToDictionaryKey, TMapToDictionaryValue> $closure
     * @return self<TMapToDictionaryKey, TMapToDictionaryValue[]>
     */
    #[Override]
    final public function mapToDictionary(Closure $closure): self
    {
        /** @var array<TMapToDictionaryKey, TMapToDictionaryValue[]> */
        $dictionary = [];

        foreach ($this->elements as $key => $item) {
            $pair = $closure($item, $key);

            /** @var TMapToDictionaryValue */
            $value = reset($pair);

            /** @var TMapToDictionaryKey|null */
            $key = key($pair);
            assert($key !== null);

            if (!isset($dictionary[$key])) {
                $dictionary[$key] = [];
            }

            $dictionary[$key][] = $value;
        }

        return new self($dictionary);
    }

    /**
     * Run a grouping map over the items.
     *
     * The callback should return an associative array with a single key/value pair.
     *
     * @template TMapToGroupsKey of array-key
     * @template TMapToGroupsValue
     *
     * @param  Closure(TValue,TKey): array<TMapToGroupsKey, TMapToGroupsValue> $closure
     * @return self<TMapToGroupsKey, self<int,TMapToGroupsValue>>
     */
    #[Override]
    final public function mapToGroups(Closure $closure): self
    {
        /** @var self<TMapToGroupsKey, TMapToGroupsValue[]> */
        $groups = $this->mapToDictionary($closure);

        // @phpstan-ignore-next-line
        return $groups->map(static fn ($items, $key): self => new self($items));
    }
}
