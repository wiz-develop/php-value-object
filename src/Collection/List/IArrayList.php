<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection\List;

use Closure;
use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpValueObject\Collection\Base\ICollection;
use WizDevelop\PhpValueObject\Collection\Exception\CollectionNotFoundException;
use WizDevelop\PhpValueObject\Collection\Exception\MultipleCollectionsFoundException;

/**
 * リストコレクション インターフェース
 * @template TValue
 * @uses WizDevelop\PhpValueObject\Collection\ArrayList
 * @extends ICollection<int,TValue>
 */
interface IArrayList extends ICollection
{
    /**
     * Create a new collection instance if the value isn't one already.
     *
     * @template TMakeValue of TValue
     *
     * @param  iterable<int,TMakeValue> $items
     * @return static<TMakeValue>
     */
    public static function make(iterable $items = []): static;

    /**
     * 要素の末尾を取得する
     * @template TLastDefault
     * @param  (Closure(TValue,int): bool)|null                                  $closure
     * @param  TLastDefault                                                      $default
     * @return ($default is null ? Option<TValue> : Option<TValue|TLastDefault>)
     */
    public function last(?Closure $closure = null, $default = null): Option;

    /**
     * 要素の末尾を取得する
     * 取得できない場合は例外を投げる
     * @param  (Closure(TValue,int): bool)|null $closure
     * @return TValue
     * @throws CollectionNotFoundException
     */
    public function lastOrFail(?Closure $closure = null);

    /**
     * 要素を逆順にしたコレクションを取得する
     * @return static<TValue>
     */
    public function reverse(): static;

    /**
     * 要素の先頭を取得する
     * @template TFirstDefault
     * @param  (Closure(TValue,int): bool)|null                                   $closure
     * @param  TFirstDefault                                                      $default
     * @return ($default is null ? Option<TValue> : Option<TValue|TFirstDefault>)
     */
    public function first(?Closure $closure, $default = null): Option;

    /**
     * 要素の先頭を取得する
     * 取得できない場合は例外を投げる
     * @param  (Closure(TValue,int): bool)|null $closure
     * @return TValue
     * @throws CollectionNotFoundException
     */
    public function firstOrFail(?Closure $closure = null);

    /**
     * 要素を必ず1件取得する
     * @param  (Closure(TValue,int): bool)|null  $closure
     * @return TValue
     * @throws CollectionNotFoundException
     * @throws MultipleCollectionsFoundException
     */
    public function sole(?Closure $closure = null);

    /**
     * 指定した範囲の要素を切り取ったコレクション取得する
     * @return static<TValue>
     */
    public function slice(int $offset, ?int $length = null): static;

    /**
     * 1つ以上のアイテムをコレクション末尾に追加したコレクションを取得する
     *
     * @param  TValue         ...$values
     * @return static<TValue>
     */
    public function push(...$values): static;

    /**
     * 引数で受け取ったコレクションの要素を現在のコレクションの末尾に連結します。
     * pushが単一要素を追加するのに対し、concatは複数の要素（コレクション全体）を一度に追加できます。
     *
     * @template TValue2
     *
     * @param  IArrayList<TValue2>        $other
     * @return IArrayList<TValue|TValue2>
     */
    public function concat(self $other): self;

    /**
     * 引数で受け取ったコレクションを現在のコレクションと結合します。
     * concatとの違いは、キーを維持・統合する点です。
     * 連想配列（キー付き配列）の場合、同じキーが存在すると後から渡した配列の値で上書きされ、異なるキーはそのまま追加されます。
     * 数値インデックスの場合は、PHPのarray_mergeと同様に後からの要素が順番に追加されます（キーは再割り当てされます）
     *
     * @template TValue2
     *
     * @param  IArrayList<TValue2>        $other
     * @return IArrayList<TValue|TValue2>
     */
    public function merge(self $other): self;

    /**
     * @template TMapValue
     *
     * @param  Closure(TValue,int): TMapValue $closure
     * @return IArrayList<TMapValue>
     */
    public function map(Closure $closure): self;

    /**
     * 各要素に関数を適用し、結果を平坦化したコレクションを返す
     * @template TFlatMapValue
     * @param  Closure(TValue,int): iterable<TFlatMapValue> $closure
     * @return self<TFlatMapValue>
     */
    public function flatMap(Closure $closure): self;

    /**
     * @param  Closure(TValue,int): TValue $closure
     * @return static<TValue>
     */
    public function mapStrict(Closure $closure): static;

    /**
     * 与えられた真理判定に合格するすべての要素のコレクションを作成する。
     * @param  Closure(TValue,int): bool $closure
     * @return self<TValue>
     */
    public function filter(Closure $closure): self;

    /**
     * 与えられたクラスのインスタンスのみを含むコレクションを作成する。
     *
     * @template TFilterValue of TValue
     * @param  class-string<TFilterValue> $innerClass
     * @return self<TFilterValue>
     */
    public function filterAs(string $innerClass): self;

    /**
     * キーが連続した整数にリセットされた新しいコレクションを作成する。
     * @return self<TValue>
     */
    public function values(): self;

    /**
     * 与えられた真理判定に合格するすべての要素のコレクションを作成する。
     * (strict version - 正確な型を保持)
     * @param  Closure(TValue,int): bool $closure
     * @return static<TValue>
     */
    public function filterStrict(Closure $closure): static;

    /**
     * 与えられた真理判定に合格しないすべての要素のコレクションを作成する。
     * @param  Closure(TValue,int): bool $closure
     * @return static<TValue>
     */
    public function reject(Closure $closure): static;

    /**
     * コレクション配列から一意な項目のみを返します。
     * @param  Closure(TValue,int): mixed $closure
     * @return static<TValue>
     */
    public function unique(?Closure $closure = null): static;

    /**
     * @template TCarry
     * @param  Closure(TCarry,TValue,int): TCarry $closure
     * @param  TCarry                             $initial
     * @return TCarry
     */
    public function reduce(Closure $closure, $initial = null);

    /**
     * コレクションに指定した要素が含まれているかどうかを判定する
     * @param (Closure(TValue,int): bool)|TValue $key
     */
    public function contains($key): bool;

    /**
     * コレクションの要素が全て指定した真理判定/値に合格するかどうかを判定する
     * @param (Closure(TValue,int): bool)|TValue $key
     */
    public function every($key): bool;

    /**
     * 指定した要素を末尾に追加したコレクションを取得する
     * @param  TValue         $element
     * @return static<TValue>
     */
    public function add($element): static;

    /**
     * Sort through each item with a callback.
     *
     * @param  (Closure(TValue,TValue): int)|null $closure
     * @return static<TValue>
     */
    public function sort(?Closure $closure = null): static;
}
