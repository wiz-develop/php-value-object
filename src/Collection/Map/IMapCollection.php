<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection\Map;

use Closure;
use WizDevelop\PhpValueObject\Collection\Base\ICollection;
use WizDevelop\PhpValueObject\Collection\CollectionNotFoundException;
use WizDevelop\PhpValueObject\Collection\ListCollection;
use WizDevelop\PhpValueObject\Collection\MultipleCollectionsFoundException;
use WizDevelop\PhpValueObject\Pair;

/**
 * マップコレクション インターフェース
 * @template TKey
 * @template TValue
 * @uses WizDevelop\PhpValueObject\Collection\Map\MapCollectionDefault
 * @uses WizDevelop\PhpValueObject\Collection\MapCollection
 * @extends ICollection<TKey,TValue>
 */
interface IMapCollection extends ICollection
{
    /**
     * Create a new collection instance if the value isn't one already.
     *
     * @template TMakeKey of TKey
     * @template TMakeValue of TValue
     *
     * @param  iterable<TMakeKey,TMakeValue> $items
     * @return static<TMakeKey,TMakeValue>
     */
    public static function make(iterable $items = []): static;

    /**
     * 要素の末尾を取得する
     * @template TLastDefault
     * @param  (Closure(TValue,TKey): bool)|null $closure
     * @param  TLastDefault                      $default
     * @return Pair<TKey,TValue>|TLastDefault
     */
    public function last(?Closure $closure = null, $default = null);

    /**
     * 要素の末尾を取得する
     * 取得できない場合は例外を投げる
     * @param  (Closure(TValue,TKey): bool)|null $closure
     * @return Pair<TKey,TValue>
     * @throws CollectionNotFoundException
     */
    public function lastOrFail(?Closure $closure = null): Pair;

    /**
     * 要素を逆順にしたコレクションを取得する
     * @return static<TKey,TValue>
     */
    public function reverse(): static;

    /**
     * 要素の先頭を取得する
     * @template TFirstDefault
     * @param  (Closure(TValue,TKey): bool)|null $closure
     * @param  TFirstDefault                     $default
     * @return Pair<TKey,TValue>|TFirstDefault
     */
    public function first(?Closure $closure, $default = null);

    /**
     * 要素の先頭を取得する
     * 取得できない場合は例外を投げる
     * @param  (Closure(TValue,TKey): bool)|null $closure
     * @return Pair<TKey,TValue>
     * @throws CollectionNotFoundException
     */
    public function firstOrFail(?Closure $closure = null): Pair;

    /**
     * 要素を必ず1件取得する
     * @param  (Closure(TValue,TKey): bool)|null $closure
     * @return Pair<TKey,TValue>
     * @throws CollectionNotFoundException
     * @throws MultipleCollectionsFoundException
     */
    public function sole(?Closure $closure = null): Pair;

    /**
     * 指定した範囲の要素を切り取ったコレクション取得する
     * @return static<TKey,TValue>
     */
    public function slice(int $offset, ?int $length = null): static;

    /**
     * Associates a key with a value, replacing a previous association if there
     * was one.
     *
     * @param  TKey                $key
     * @param  TValue              $value
     * @return static<TKey,TValue>
     */
    public function put($key, $value): static;

    /**
     * Creates associations for all keys and corresponding values of either an
     * array or iterable object.
     *
     * @param  iterable<TKey,TValue> $values
     * @return static<TKey,TValue>
     */
    public function putAll(iterable $values): static;

    /**
     * Returns the value associated with a key, or an optional default if the
     * key is not associated with a value.
     *
     * @template TDefault
     *
     * @param  TKey            $key
     * @param  TDefault        $default
     * @return TValue|TDefault
     */
    public function get($key, $default = null);

    /**
     * 引数で受け取ったコレクションを現在のコレクションと結合します。
     * concatとの違いは、キーを維持・統合する点です。
     * 連想配列（キー付き配列）の場合、同じキーが存在すると後から渡した配列の値で上書きされ、異なるキーはそのまま追加されます。
     * 数値インデックスの場合は、PHPのarray_mergeと同様に後からの要素が順番に追加されます（キーは再割り当てされます）
     *
     * @template TKey2
     * @template TValue2
     *
     * @param  IMapCollection<TKey2,TValue2>             $other
     * @return IMapCollection<TKey|TKey2,TValue|TValue2>
     */
    public function merge(self $other): self;

    /**
     * @template TMapValue
     *
     * @param  Closure(TValue,TKey): TMapValue $closure
     * @return IMapCollection<TKey,TMapValue>
     */
    public function map(Closure $closure): self;

    /**
     * @param  Closure(TValue,TKey): TValue $closure
     * @return static<TKey,TValue>
     */
    public function mapStrict(Closure $closure): static;

    /**
     * 与えられた真理判定に合格するすべての要素のコレクションを作成する。
     * @param  Closure(TValue,TKey): bool $closure
     * @return static<TKey,TValue>
     */
    public function filter(Closure $closure): static;

    /**
     * 与えられた真理判定に合格しないすべての要素のコレクションを作成する。
     * @param  Closure(TValue,TKey): bool $closure
     * @return static<TKey,TValue>
     */
    public function reject(Closure $closure): static;

    /**
     * @template TReduceInitial
     * @template TReduceReturnType
     * @param  Closure(TReduceInitial|TReduceReturnType,TValue,TKey): TReduceReturnType $closure
     * @param  TReduceInitial                                                           $initial
     * @return TReduceReturnType
     */
    public function reduce(Closure $closure, $initial = null);

    /**
     * マップに指定したキーが含まれているかどうかを判定する
     * @param TKey $key
     */
    public function has($key): bool;

    /**
     * Sort through each item with a callback.
     *
     * @param  (Closure(TValue,TValue): int)|null $closure
     * @return static<TKey,TValue>
     */
    public function sort(?Closure $closure = null): static;

    /**
     * Returns a List of all the associated values in the Map.
     *
     * @return ListCollection<TValue>
     */
    public function values(): ListCollection;
}
