<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection\Array;

use Closure;

/**
 * @template TKey of array-key
 * @template TValue
 * @uses WizDevelop\PhpValueObject\Collection\Array\ArrayCollectionDefault
 * @uses WizDevelop\PhpValueObject\Collection\ArrayCollection
 */
interface IArrayCollection
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
    public static function make(self|iterable $items): static;

    /**
     * Map a collection and flatten the result by a single level.
     *
     * @template TFlatMapKey of array-key
     * @template TFlatMapValue
     *
     * @param  Closure(TValue, TKey): (self<TFlatMapKey, TFlatMapValue>|array<TFlatMapKey, TFlatMapValue>) $closure
     * @return self<TFlatMapKey,TFlatMapValue>
     */
    public function flatMap(Closure $closure): self;

    /**
     * Collapse the collection of items into a single array.
     * @return self<TKey,mixed>
     */
    public function collapse(): self;

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
    public function mapToDictionary(Closure $closure): self;

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
    public function mapToGroups(Closure $closure): self;
}
