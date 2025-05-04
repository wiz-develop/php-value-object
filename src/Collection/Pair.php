<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection;

use WizDevelop\PhpValueObject\IValueObject;
use WizDevelop\PhpValueObject\ValueObjectDefault;

/**
 * A pair which represents a key and an associated value.
 *
 * @template TKey
 * @template TValue
 */
readonly class Pair implements IValueObject
{
    use ValueObjectDefault;

    /**
     * Creates a new instance.
     *
     * @param TKey   $key
     * @param TValue $value
     */
    final private function __construct(
        public mixed $key,
        public mixed $value
    ) {
    }

    /**
     * @template TOfKey
     * @template TOfValue
     *
     * @param  TOfKey                 $key
     * @param  TOfValue               $value
     * @return self<TOfKey, TOfValue>
     */
    final public static function of(
        mixed $key,
        mixed $value
    ): self {
        return new self($key, $value);
    }

    /**
     * Returns a copy of the Pair
     *
     * @return self<TKey, TValue>
     */
    final public function copy(): self
    {
        return new self($this->key, $this->value);
    }

    /**
     * @return array{key: TKey, value: TValue}
     */
    final public function toArray(): array
    {
        return [
            'key'   => $this->key,
            'value' => $this->value,
        ];
    }
}
