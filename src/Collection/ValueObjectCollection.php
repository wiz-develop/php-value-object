<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection;

use WizDevelop\PhpValueObject\IValueObject;

/**
 * @template TValue of IValueObject
 * @extends ListCollection<TValue>
 */
readonly class ValueObjectCollection extends ListCollection
{
    /**
     * 存在するかどうか
     * @param TValue $element
     */
    public function has(IValueObject $element): bool
    {
        return parent::contains(static fn (IValueObject $e) => $e->equals($element));
    }

    /**
     * 指定した値オブジェクトを削除し、コレクションを取得する
     * @param TValue $element
     */
    public function remove(IValueObject $element): static
    {
        return $this->filter(static fn (IValueObject $e) => !$e->equals($element));
    }

    /**
     * 指定した値オブジェクトを置換し、コレクションを取得する
     * @param TValue $element
     */
    public function put(IValueObject $element): static
    {
        return $this->mapStrict(static fn (IValueObject $e) => $e->equals($element) ? $element : $e);
    }
}
