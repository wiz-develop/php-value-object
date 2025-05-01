<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection;

use ArrayAccess;
use Closure;
use Generator;
use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Collection\Base\ArrayAccessDefault;
use WizDevelop\PhpValueObject\Collection\Base\CollectionBase;
use WizDevelop\PhpValueObject\Collection\Base\CollectionDefault;
use WizDevelop\PhpValueObject\Collection\Base\CountableDefault;
use WizDevelop\PhpValueObject\Collection\Exception\CollectionNotFoundException;
use WizDevelop\PhpValueObject\Collection\Exception\MultipleCollectionsFoundException;
use WizDevelop\PhpValueObject\Collection\List\IListCollection;
use WizDevelop\PhpValueObject\Collection\List\IListCollectionFactory;

/**
 * リストコレクション
 * @template TValue
 * @extends CollectionBase<int,TValue>
 * @implements IListCollection<TValue>
 * @implements IListCollectionFactory<TValue>
 * @implements ArrayAccess<int,TValue>
 */
readonly class ListCollection extends CollectionBase implements IListCollection, IListCollectionFactory, ArrayAccess
{
    /** @use ArrayAccessDefault<int,TValue> */
    use ArrayAccessDefault;

    /** @use CollectionDefault<int, TValue> */
    use CollectionDefault;
    use CountableDefault;

    /**
     * @param array<int,TValue> $elements
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
     * @return Generator<int,TValue>
     */
    #[Override]
    final public function getIterator(): Generator
    {
        foreach ($this->elements as $element) {
            yield $element;
        }
    }

    // -------------------------------------------------------------------------
    // NOTE: ICollection
    // -------------------------------------------------------------------------
    #[Override]
    final public function toArray(): array
    {
        return $this->elements;
    }

    // -------------------------------------------------------------------------
    // NOTE: IListCollectionFactory
    // -------------------------------------------------------------------------
    #[Override]
    final public static function from(array $elements): static
    {
        return new static($elements);
    }

    #[Override]
    final public static function tryFrom(array $elements): Result
    {
        return static::isValidCount($elements)
            ->andThen(static fn () => Result\ok(static::from($elements)));
    }

    #[Override]
    final public static function empty(): static
    {
        /** @var array<int,TValue> */
        $elements = [];

        return new static($elements);
    }

    // -------------------------------------------------------------------------
    // NOTE: IListCollection
    // -------------------------------------------------------------------------
    #[Override]
    final public static function make(iterable $items = []): static
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

    /**
     * @template TValue2
     *
     * @param  IListCollection<TValue2> $other
     * @return self<TValue|TValue2>
     */
    #[Override]
    final public function concat(IListCollection $other): self
    {
        $elements = $this->elements;

        foreach ($other as $element) {
            $elements[] = $element;
        }

        return new self($elements);
    }

    /**
     * @template TValue2
     *
     * @param  IListCollection<TValue2> $other
     * @return self<TValue|TValue2>
     */
    #[Override]
    final public function merge(IListCollection $other): self
    {
        return new self(array_merge($this->elements, $other->toArray()));
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

    /**
     * @template TReduceInitial
     * @template TReduceReturnType
     * @param  Closure(TReduceInitial|TReduceReturnType,TValue,int): TReduceReturnType $closure
     * @param  TReduceInitial                                                          $initial
     * @return TReduceReturnType
     */
    #[Override]
    final public function reduce(Closure $closure, $initial = null)
    {
        /** @var TReduceReturnType */
        $carry = $initial;

        foreach ($this->elements as $index => $value) {
            $carry = $closure($carry, $value, $index);
        }

        return $carry;
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
