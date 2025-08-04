<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection;

use ArrayAccess;
use Closure;
use Generator;
use LogicException;
use Override;
use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Collection\Base\ArrayAccessDefault;
use WizDevelop\PhpValueObject\Collection\Base\CollectionBase;
use WizDevelop\PhpValueObject\Collection\Base\CollectionDefault;
use WizDevelop\PhpValueObject\Collection\Base\CountableDefault;
use WizDevelop\PhpValueObject\Collection\Base\ICollection;
use WizDevelop\PhpValueObject\Collection\Exception\CollectionNotFoundException;
use WizDevelop\PhpValueObject\Collection\Exception\MultipleCollectionsFoundException;
use WizDevelop\PhpValueObject\Collection\List\IArrayList;
use WizDevelop\PhpValueObject\Collection\List\IArrayListFactory;
use WizDevelop\PhpValueObject\Error\IErrorValue;
use WizDevelop\PhpValueObject\Error\ValueObjectError;

/**
 * リストコレクション
 * @template TValue
 * @extends CollectionBase<int,TValue>
 * @implements IArrayList<TValue>
 * @implements IArrayListFactory<TValue>
 * @implements ArrayAccess<int,TValue>
 */
readonly class ArrayList extends CollectionBase implements IArrayList, IArrayListFactory, ArrayAccess
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
    // NOTE: IArrayListFactory
    // -------------------------------------------------------------------------
    #[Override]
    final public static function from(iterable $elements): static
    {
        if (is_array($elements)) {
            return new static($elements);
        }

        return new static(iterator_to_array($elements));
    }

    #[Override]
    final public static function tryFrom(iterable $elements): Result
    {
        $elements = is_array($elements) ? $elements : iterator_to_array($elements);

        return static::isValid($elements)
            ->andThen(static fn () => static::isValidCount($elements))
            ->andThen(static fn () => Result\ok(new static($elements)));
    }

    /**
     * 信頼できないResult型のプリミティブ値からインスタンスを生成する
     *
     * @template TTryFromValue of TValue
     *
     * @param  iterable<int,(Result<TTryFromValue,IErrorValue>|Result<TTryFromValue,IErrorValue[]>|Result)> $results
     * @return Result<static<TTryFromValue>,ValueObjectError>
     */
    /**
     * @phpstan-ignore-next-line
     */
    #[Override]
    final public static function tryFromResults(iterable $results): Result
    {
        $elements = is_array($results) ? $results : iterator_to_array($results);

        $elementsResult = Result\combineWithErrorValue(...$elements);
        if ($elementsResult->isErr()) {
            $flattenErrs = [];
            foreach ($elementsResult->unwrapErr() as $err) {
                if ($err instanceof IErrorValue) { // @phpstan-ignore-line
                    $flattenErrs[] = $err;
                } elseif (is_array($err)) {
                    foreach ($err as $e) {
                        if ($e instanceof IErrorValue) { // @phpstan-ignore-line
                            $flattenErrs[] = $e;
                        } else {
                            throw new LogicException(
                                'Invalid error value type in array. Expected IErrorValue.',
                            );
                        }
                    }
                } else {
                    throw new LogicException(
                        'Invalid error value type. Expected IErrorValue or array of IErrorValue.',
                    );
                }
            }

            return Result\err(ValueObjectError::collection()->invalidElementValues(
                static::class,
                ...$flattenErrs,
            ));
        }

        $elements = array_map(static fn ($result) => $result->unwrap(), $elements);

        // @phpstan-ignore return.type
        return static::isValid($elements)
            ->andThen(static fn () => static::isValidCount($elements))
            ->andThen(static fn () => Result\ok(new static($elements)));
    }

    #[Override]
    final public static function empty(): static
    {
        /** @var array<int,TValue> */
        $elements = [];

        return new static($elements);
    }

    // -------------------------------------------------------------------------
    // NOTE: IArrayList
    // -------------------------------------------------------------------------
    #[Override]
    final public static function make(iterable $items = []): static
    {
        if (is_array($items)) {
            return new static($items);
        }

        return new static(iterator_to_array($items));
    }

    /**
     * @phpstan-ignore-next-line
     */
    #[Override]
    final public function last(?Closure $closure = null, $default = null): Option
    {
        return Option\of(function () use ($closure, $default) {
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
        });
    }

    #[Override]
    final public function lastOrFail(?Closure $closure = null)
    {
        return $this->last($closure)->unwrapOrThrow(new CollectionNotFoundException(static::class));
    }

    #[Override]
    final public function reverse(): static
    {
        return new static(array_reverse($this->elements));
    }

    /**
     * @phpstan-ignore-next-line
     */
    #[Override]
    final public function first(?Closure $closure = null, $default = null): Option
    {
        return Option\of(function () use ($closure, $default) {
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
        });
    }

    #[Override]
    final public function firstOrFail(?Closure $closure = null)
    {
        return $this->first($closure)->unwrapOrThrow(new CollectionNotFoundException(static::class));
    }

    #[Override]
    final public function sole(?Closure $closure = null)
    {
        $items = $closure === null ? new static($this->elements) : $this->filterStrict($closure);
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
     * @param  IArrayList<TValue2>  $other
     * @return self<TValue|TValue2>
     */
    #[Override]
    final public function concat(IArrayList $other): self
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
     * @param  IArrayList<TValue2>  $other
     * @return self<TValue|TValue2>
     */
    #[Override]
    final public function merge(IArrayList $other): self
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

    /**
     * @template TFlatMapValue
     * @param  Closure(TValue,int): iterable<TFlatMapValue> $closure
     * @return self<TFlatMapValue>
     */
    #[Override]
    final public function flatMap(Closure $closure): self
    {
        return $this->map($closure)->collapse(); // @phpstan-ignore-line
    }

    /**
     * @return self<mixed>
     */
    #[Override]
    final public function flatten(int $depth = PHP_INT_MAX): self
    {
        $elements = $this->elements;
        $flattened = self::flattenInner($elements, $depth);

        return new self($flattened); // @phpstan-ignore-line
    }

    /**
     * 多次元配列を単一レベルに平坦化する。
     *
     * @param  iterable<mixed> $array
     * @return array<mixed>
     */
    private static function flattenInner(iterable $array, int $depth = PHP_INT_MAX): array
    {
        $result = [];

        foreach ($array as $item) {
            $item = $item instanceof ICollection ? $item->toArray() : $item;

            if (! is_array($item)) {
                $result[] = $item;
            } else {
                $values = $depth === 1
                    ? array_values($item)
                    : self::flattenInner($item, $depth - 1);

                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * 配列の配列を 1 つの配列に折りたたむ。
     *
     * @return self<mixed>
     */
    private function collapse(): self
    {
        $elements = $this->elements;
        $results = [];

        foreach ($elements as $values) {
            if ($values instanceof ICollection) {
                $values = $values->toArray();
            } elseif (! is_array($values)) {
                continue;
            }

            $results[] = $values;
        }

        return new self(array_values(array_merge([], ...$results)));
    }

    /**
     * @param  Closure(TValue,int): bool $closure
     * @return self<TValue>
     */
    #[Override]
    final public function filter(Closure $closure): self
    {
        return new self(array_filter($this->elements, $closure, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * @template TFilterValue of TValue
     * @param  class-string<TFilterValue> $innerClass
     * @return self<TFilterValue>
     */
    #[Override]
    final public function filterAs(string $innerClass): self
    {
        // @phpstan-ignore-next-line
        return new self(array_filter(
            $this->elements,
            static fn ($value) => $value instanceof $innerClass,
            ARRAY_FILTER_USE_BOTH,
        ));
    }

    /**
     * @return self<TValue>
     */
    #[Override]
    final public function values(): self
    {
        return new self(array_values($this->elements));
    }

    #[Override]
    final public function filterStrict(Closure $closure): static
    {
        return new static(array_filter($this->elements, $closure, ARRAY_FILTER_USE_BOTH));
    }

    #[Override]
    final public function reject(Closure $closure): static
    {
        return $this->filterStrict(static fn ($value, $key) => !$closure($value, $key));
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
     * @template TCarry
     * @param  Closure(TCarry,TValue,int): TCarry $closure
     * @param  TCarry                             $initial
     * @return TCarry
     */
    #[Override]
    final public function reduce(Closure $closure, $initial = null)
    {
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
            return $this->first($key)->isSome();
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

        if ($closure === null) {
            asort($elements, SORT_REGULAR);
        } else {
            uasort($elements, $closure);
        }

        return new static($elements);
    }
}
