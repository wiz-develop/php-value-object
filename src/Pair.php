<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject;

/**
 * A pair which represents a key and an associated value.
 *
 * @template TKey
 * @template TValue
 */
final readonly class Pair implements IValueObject
{
    use ValueObjectDefault;

    /**
     * Creates a new instance.
     *
     * @param TKey   $key
     * @param TValue $value
     */
    private function __construct(
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
    public static function of(
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
    public function copy(): self
    {
        return new self($this->key, $this->value);
    }

    /**
     * @return array{key: TKey, value: TValue}
     */
    public function toArray(): array
    {
        return [
            'key'   => $this->key,
            'value' => $this->value,
        ];
    }
}
