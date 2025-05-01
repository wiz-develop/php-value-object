<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection;

use ArrayAccess;
use BadMethodCallException;
use Closure;
use Generator;
use OutOfBoundsException;
use Override;
use Stringable;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Collection\Base\CollectionBase;
use WizDevelop\PhpValueObject\Collection\Base\CollectionDefault;
use WizDevelop\PhpValueObject\Collection\Base\CountableDefault;
use WizDevelop\PhpValueObject\Collection\Exception\CollectionNotFoundException;
use WizDevelop\PhpValueObject\Collection\Exception\MultipleCollectionsFoundException;
use WizDevelop\PhpValueObject\Collection\Map\IMapCollection;
use WizDevelop\PhpValueObject\Collection\Map\IMapCollectionFactory;
use WizDevelop\PhpValueObject\IValueObject;

/**
 * マップコレクション
 * @template TKey
 * @template TValue
 * @extends CollectionBase<int, Pair<TKey,TValue>>
 * @implements IMapCollection<TKey,TValue>
 * @implements IMapCollectionFactory<TKey,TValue>
 * @implements ArrayAccess<TKey,TValue>
 */
readonly class MapCollection extends CollectionBase implements IMapCollection, IMapCollectionFactory, ArrayAccess
{
    /** @use CollectionDefault<TKey,TValue> */
    use CollectionDefault;
    use CountableDefault;

    /**
     * @param array<int,Pair<TKey,TValue>> $elements
     */
    final private function __construct(array $elements)
    {
        parent::__construct($elements);
    }

    #[Override]
    protected static function minCount(): int
    {
        return self::MIN_COUNT;
    }

    #[Override]
    protected static function maxCount(): int
    {
        return self::MAX_COUNT;
    }

    // -------------------------------------------------------------------------
    // NOTE: IteratorAggregate
    // -------------------------------------------------------------------------
    /**
     * @return Generator<TKey,TValue>
     */
    #[Override]
    final public function getIterator(): Generator
    {
        foreach ($this->elements as $pair) {
            yield $pair->key => $pair->value;
        }
    }

    // -------------------------------------------------------------------------
    // NOTE: ArrayAccess
    // -------------------------------------------------------------------------
    #[Override]
    final public function offsetSet($offset, $value): void
    {
        throw new BadMethodCallException('MapCollection does not support offsetSet due to its immutable.');
    }

    /**
     * @throws OutOfBoundsException
     */
    /**
     * @phpstan-ignore-next-line
     */
    #[Override]
    final public function offsetGet($offset): mixed
    {
        $value = $this->get($offset);

        if ($value === null) {
            throw new OutOfBoundsException();
        }

        return $value;
    }

    #[Override]
    final public function offsetUnset($offset): void
    {
        throw new BadMethodCallException('MapCollection does not support offsetUnset due to its immutable.');
    }

    #[Override]
    final public function offsetExists($offset): bool
    {
        return $this->get($offset) !== null;
    }

    // -------------------------------------------------------------------------
    // NOTE: ICollection
    // -------------------------------------------------------------------------
    #[Override]
    final public function toArray(): array
    {
        // @phpstan-ignore-next-line
        return array_reduce($this->elements, static function (array $carry, Pair $pair) {
            $key = match(true) {
                is_int($pair->key) => $pair->key,
                is_string($pair->key) => $pair->key,
                $pair->key instanceof Stringable => (string)$pair->key,
                default => throw new BadMethodCallException('The key must be an integer or string or Stringable.'),
            };
            $carry[$key] = $pair->value;

            return $carry;
        }, []);
    }

    // -------------------------------------------------------------------------
    // NOTE: IMapCollectionFactory
    // -------------------------------------------------------------------------
    /**
     * @template TFromKey of TKey
     * @template TFromValue of TValue
     *
     * @param  Pair<TFromKey,TFromValue>   ...$values
     * @return static<TFromKey,TFromValue>
     */
    #[Override]
    final public static function from(Pair ...$values): static
    {
        /** @var array<int,Pair<TFromKey,TFromValue>> */
        $elements = [];

        foreach ($values as $index => $pair) {
            self::putPair($elements, $pair);
        }

        return new static($elements);
    }

    #[Override]
    final public static function tryFrom(Pair ...$values): Result
    {
        // @phpstan-ignore-next-line
        return static::isValidCount($values)
            ->andThen(static fn () => Result\ok(static::from(...$values)));
    }

    #[Override]
    final public static function empty(): static
    {
        /** @var array<int,Pair<TKey,TValue>> */
        $elements = [];

        return new static($elements);
    }

    // -------------------------------------------------------------------------
    // NOTE: IMapCollection
    // -------------------------------------------------------------------------
    /**
     * @template TMakeKey of TKey
     * @template TMakeValue of TValue
     *
     * @param  iterable<TMakeKey,TMakeValue> $items
     * @return static<TMakeKey,TMakeValue>
     */
    #[Override]
    final public static function make(iterable $items = []): static
    {
        /** @var array<int,Pair<TMakeKey,TMakeValue>> */
        $elements = [];

        foreach ($items as $key => $value) {
            $puttingPair = Pair::of($key, $value);
            self::putPair($elements, $puttingPair);
        }

        return new static($elements);
    }

    #[Override]
    final public function last(?Closure $closure = null, $default = null)
    {
        if ($closure === null) {
            return $this->elements[count($this->elements) - 1] ?? $default;
        }

        foreach (array_reverse($this->elements) as $index => $pair) {
            if ($closure($pair->value, $pair->key)) {
                return $pair;
            }
        }

        return $default;
    }

    #[Override]
    final public function lastOrFail(?Closure $closure = null): Pair
    {
        $element = $this->last($closure);

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
            return $this->elements[0] ?? $default;
        }

        foreach ($this->elements as $index => $pair) {
            if ($closure($pair->value, $pair->key)) {
                return $pair;
            }
        }

        return $default;
    }

    #[Override]
    final public function firstOrFail(?Closure $closure = null): Pair
    {
        $element = $this->first($closure);

        if ($element !== null) {
            return $element;
        }

        throw new CollectionNotFoundException(static::class);
    }

    #[Override]
    final public function sole(?Closure $closure = null): Pair
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
    final public function put($key, $value): static
    {
        $elements = $this->elements;
        $puttingPair = Pair::of($key, $value);
        self::putPair($elements, $puttingPair);

        return new static($elements);
    }

    #[Override]
    final public function putAll(iterable $values): static
    {
        $elements = $this->elements;

        foreach ($values as $key => $value) {
            $puttingPair = Pair::of($key, $value);
            self::putPair($elements, $puttingPair);
        }

        return new static($elements);
    }

    #[Override]
    final public function get($key, $default = null)
    {
        $elements = $this->elements;
        $foundKey = self::findIndex($elements, $key);

        if ($foundKey !== null) {
            return $elements[$foundKey]->value;
        }

        return $default;
    }

    /**
     * @template TKey2
     * @template TValue2
     *
     * @param  self<TKey2,TValue2>             $other
     * @return self<TKey|TKey2,TValue|TValue2>
     */
    #[Override]
    final public function merge(IMapCollection $other): self
    {
        /** @var array<int,Pair<TKey|TKey2,TValue|TValue2>> */
        $elements = $this->elements;

        foreach ($other as $key => $value) {
            /** @var Pair<TKey|TKey2,TValue|TValue2> */
            $puttingPair = Pair::of($key, $value);
            self::putPair($elements, $puttingPair);
        }

        return new self($elements);
    }

    /**
     * @template TMapValue
     *
     * @param  Closure(TValue,TKey): TMapValue $closure
     * @return self<TKey,TMapValue>
     */
    #[Override]
    final public function map(Closure $closure): self
    {
        /** @var array<int,Pair<TKey,TMapValue>> */
        $elements = [];

        foreach ($this->elements as $index => $pair) {
            /** @var Pair<TKey,TMapValue> */
            $mappedPair = Pair::of($pair->key, $closure($pair->value, $pair->key));
            $elements[$index] = $mappedPair;
        }

        return new self($elements);
    }

    #[Override]
    final public function mapStrict(Closure $closure): static
    {
        /** @var array<int,Pair<TKey,TValue>> */
        $elements = [];

        foreach ($this->elements as $index => $pair) {
            /** @var Pair<TKey,TValue> */
            $mappedPair = Pair::of($pair->key, $closure($pair->value, $pair->key));
            $elements[$index] = $mappedPair;
        }

        return new static($elements);
    }

    #[Override]
    final public function filter(Closure $closure): static
    {
        /** @var array<int,Pair<TKey,TValue>> */
        $elements = [];

        foreach ($this->elements as $index => $pair) {
            if ($closure($pair->value, $pair->key)) {
                $elements[$index] = $pair;
            }
        }

        return new static($elements);
    }

    #[Override]
    final public function reject(Closure $closure): static
    {
        return $this->filter(static fn ($value, $key) => !$closure($value, $key));
    }

    /**
     * @template TReduceInitial
     * @template TReduceReturnType
     * @param  Closure(TReduceInitial|TReduceReturnType,TValue,TKey): TReduceReturnType $closure
     * @param  TReduceInitial                                                           $initial
     * @return TReduceReturnType
     */
    #[Override]
    final public function reduce(Closure $closure, $initial = null)
    {
        /** @var TReduceReturnType */
        $carry = $initial;

        foreach ($this->elements as $index => $pair) {
            $carry = $closure($carry, $pair->value, $pair->key);
        }

        return $carry;
    }

    #[Override]
    final public function has($key): bool
    {
        return self::findIndex($this->elements, $key) !== null;
    }

    #[Override]
    final public function sort(?Closure $closure = null): static
    {
        $elements = $this->elements;

        if ($closure) {
            usort($elements, static fn ($a, $b) => $closure($a->value, $b->value));
        } else {
            usort($elements, static fn ($a, $b) => $a->value <=> $b->value);
        }

        return new static($elements);
    }

    #[Override]
    final public function values(): ListCollection
    {
        /** @var array<int,TValue> */
        $values = [];

        foreach ($this->elements as $pair) {
            $values[] = $pair->value;
        }

        return ListCollection::make($values);
    }

    #[Override]
    public function keys(): ListCollection
    {
        /** @var array<int,TKey> */
        $keys = [];

        foreach ($this->elements as $pair) {
            $keys[] = $pair->key;
        }

        return ListCollection::make($keys);
    }

    #[Override]
    public function remove($key): static
    {
        $elements = $this->elements;
        $foundIndex = self::findIndex($elements, $key);

        if ($foundIndex !== null) {
            unset($elements[$foundIndex]);
        }

        return new static($elements);
    }

    // -------------------------------------------------------------------------
    // NOTE: private methods
    // -------------------------------------------------------------------------
    /**
     * Determines whether two keys are equal.
     *
     * @template TKeysAreEqualKey
     *
     * @param TKeysAreEqualKey $a
     * @param TKeysAreEqualKey $b
     */
    private static function keysAreEqual($a, $b): bool
    {
        if ($a instanceof IValueObject && $b instanceof IValueObject) {
            return $a::class === $b::class && $a->equals($b);
        }

        return $a === $b;
    }

    /**
     * Attempts to look up a key in the table.
     *
     * @template TFindKeyKey
     * @template TFindKeyValue
     * @template TFindKey2
     *
     * @param  array<int,Pair<TFindKeyKey,TFindKeyValue>> $elements
     * @param  TFindKey2                                  $key
     * @return int|null
     */
    private static function findIndex(array $elements, $key)
    {
        return array_find_key($elements, static function ($pair) use ($key) {
            return self::keysAreEqual($pair->key, $key);
        });
    }

    /**
     * Associates a key with a value, replacing a previous association if there
     * was one.
     *
     * @template TPutInnerKey1
     * @template TPutInnerValue1
     * @template TPutInnerKey2
     * @template TPutInnerValue2
     *
     * @param array<int,Pair<TPutInnerKey1,TPutInnerValue1>> &$elements
     * @param Pair<TPutInnerKey2,TPutInnerValue2>            &$pair
     */
    private static function putPair(array &$elements, &$pair): void
    {
        $foundIndex = self::findIndex($elements, $pair->key);

        if ($foundIndex !== null) {
            // @phpstan-ignore-next-line
            $elements[$foundIndex] = $pair;
        } else {
            // @phpstan-ignore-next-line
            $elements[] = $pair;
        }
    }
}
