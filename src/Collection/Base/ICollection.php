<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection\Base;

use Countable;
use IteratorAggregate;

/**
 * @template TKey
 * @template TValue
 * @uses WizDevelop\PhpValueObject\Collection\Base\CollectionDefault
 * @extends IteratorAggregate<TKey,TValue>
 */
interface ICollection extends Countable, IteratorAggregate
{
    /**
     * コレクションが空かどうかを判定する
     */
    public function isEmpty(): bool;

    /**
     * 要素を取得する
     * @return array<TKey,TValue>
     */
    public function toArray(): array;
}
