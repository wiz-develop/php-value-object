<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection\Base;

use Override;
use Stringable;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\IValueObject;

use function count;

/**
 * 不変コレクション
 * @template TKey
 * @template TValue
 */
abstract readonly class CollectionBase implements IValueObject, Stringable
{
    final protected const int MIN_COUNT = 0;
    final protected const int MAX_COUNT = 99999999;

    /**
     * @param array<TKey,TValue> $elements
     */
    protected function __construct(protected array $elements)
    {
        assert(static::isValid($elements)->isOk());
        assert(static::isValidCount($elements)->isOk());
    }

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
     * @return array<TKey,TValue>
     */
    #[Override]
    final public function jsonSerialize(): array
    {
        return $this->elements;
    }

    /**
     * 要素数の下限値
     * NOTE: 実装クラスでのオーバーライド用メソッド
     * @return positive-int|0
     */
    abstract protected static function minCount(): int;

    /**
     * 要素数の上限値
     * NOTE: 実装クラスでのオーバーライド用メソッド
     * @return positive-int
     */
    abstract protected static function maxCount(): int;

    /**
     * 要素数が有効か
     * @template TIsValidCountKey of TKey|array-key
     * @template TIsValidCountValue of TValue
     * @param  array<TIsValidCountKey,TIsValidCountValue> $elements
     * @return Result<bool,ValueObjectError>
     */
    final protected static function isValidCount(array $elements): Result
    {
        $element_count = count($elements);
        $min_count = static::minCount() > self::MIN_COUNT ? static::minCount() : self::MIN_COUNT;
        $max_count = static::maxCount() < self::MAX_COUNT ? static::maxCount() : self::MAX_COUNT;

        if ($element_count < $min_count && $element_count > $max_count) {
            return Result\err(ValueObjectError::collection()->invalidRange(
                className: static::class,
                min: $min_count,
                max: $max_count,
                count: $element_count,
            ));
        }
        if ($element_count < $min_count) {
            return Result\err(ValueObjectError::collection()->invalidMinCount(
                className: static::class,
                min: $min_count,
                count: $element_count,
            ));
        }
        if ($element_count > $max_count) {
            return Result\err(ValueObjectError::collection()->invalidMaxCount(
                className: static::class,
                max: $max_count,
                count: $element_count,
            ));
        }

        return Result\ok(true);
    }

    /**
     * 有効な値かどうか
     * NOTE: 実装クラスでのオーバーライド用メソッド
     *
     * @template TIsValidKey of TKey|array-key
     * @template TIsValidValue of TValue
     * @param  array<TIsValidKey,TIsValidValue> $elements
     * @return Result<bool,ValueObjectError>
     */
    protected static function isValid(array $elements): Result
    {
        return Result\ok(true);
    }
}
