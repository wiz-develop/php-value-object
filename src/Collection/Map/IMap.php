<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection\Map;

use Closure;
use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpValueObject\Collection\ArrayList;
use WizDevelop\PhpValueObject\Collection\Base\ICollection;
use WizDevelop\PhpValueObject\Collection\Exception\CollectionNotFoundException;
use WizDevelop\PhpValueObject\Collection\Exception\MultipleCollectionsFoundException;
use WizDevelop\PhpValueObject\Collection\Pair;

/**
 * マップコレクション インターフェース
 * @template TKey
 * @template TValue
 * @uses WizDevelop\PhpValueObject\Collection\Map
 * @extends ICollection<TKey,TValue>
 */
interface IMap extends ICollection
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
     * @param  (Closure(TValue,TKey): bool)|null                                                       $closure
     * @param  TLastDefault                                                                            $default
     * @return ($default is null ? Option<Pair<TKey,TValue>> : Option<Pair<TKey,TValue>|TLastDefault>)
     */
    public function last(?Closure $closure = null, $default = null): Option;

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
     * @param  (Closure(TValue,TKey): bool)|null                                                        $closure
     * @param  TFirstDefault                                                                            $default
     * @return ($default is null ? Option<Pair<TKey,TValue>> : Option<Pair<TKey,TValue>|TFirstDefault>)
     */
    public function first(?Closure $closure, $default = null): Option;

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
     * @param  TKey                                                          $key
     * @param  TDefault                                                      $default
     * @return ($default is null ? Option<TValue> : Option<TValue|TDefault>)
     */
    public function get($key, $default = null): Option;

    /**
     * 引数で受け取ったコレクションを現在のコレクションと結合します。
     * concatとの違いは、キーを維持・統合する点です。
     * 連想配列（キー付き配列）の場合、同じキーが存在すると後から渡した配列の値で上書きされ、異なるキーはそのまま追加されます。
     * 数値インデックスの場合は、PHPのarray_mergeと同様に後からの要素が順番に追加されます（キーは再割り当てされます）
     *
     * @template TKey2
     * @template TValue2
     *
     * @param  IMap<TKey2,TValue2>             $other
     * @return IMap<TKey|TKey2,TValue|TValue2>
     */
    public function merge(self $other): self;

    /**
     * @template TMapValue
     *
     * @param  Closure(TValue,TKey): TMapValue $closure
     * @return IMap<TKey,TMapValue>
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
     * 与えられたクラスのインスタンスのみを含むコレクションを作成する。
     *
     * @template TFilterValue of TValue
     * @param  class-string<TFilterValue> $innerClass
     * @return static<TKey,TFilterValue>
     */
    public function filterAs(string $innerClass): static;

    /**
     * 与えられた真理判定に合格しないすべての要素のコレクションを作成する。
     * @param  Closure(TValue,TKey): bool $closure
     * @return static<TKey,TValue>
     */
    public function reject(Closure $closure): static;

    /**
     * @template TCarry
     * @param  Closure(TCarry,TValue,TKey): TCarry $closure
     * @param  TCarry                              $initial
     * @return TCarry
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
     * @return ArrayList<TValue>
     */
    public function values(): ArrayList;

    /**
     * Returns a List of all the associated keys in the Map.
     * @return ArrayList<TKey>
     */
    public function keys(): ArrayList;

    /**
     * 指定したキーを持つ要素を削除する
     * @param  TKey                $key
     * @return static<TKey,TValue>
     */
    public function remove($key): static;
}
