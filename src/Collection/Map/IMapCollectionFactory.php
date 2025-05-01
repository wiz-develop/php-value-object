<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection\Map;

use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Collection\CollectionValueError;
use WizDevelop\PhpValueObject\Pair;

/**
 * マップコレクション ファクトリインターフェース
 * @see WizDevelop\PhpValueObject\Collection\MapCollection
 *
 * @template TKey
 * @template TValue
 */
interface IMapCollectionFactory
{
    /**
     * 信頼できるプリミティブ値からインスタンスを生成する
     *
     * @template TFromKey of TKey
     * @template TFromValue of TValue
     *
     * @param  Pair<TFromKey,TFromValue>   ...$values
     * @return static<TFromKey,TFromValue>
     */
    public static function from(Pair ...$values): static;

    /**
     * 信頼できるプリミティブ値からインスタンスを生成する
     *
     * @template TTryFromKey of TKey
     * @template TTryFromValue of TValue
     *
     * @param  Pair<TTryFromKey,TTryFromValue>                                ...$values
     * @return Result<static<TTryFromKey,TTryFromValue>,CollectionValueError>
     */
    public static function tryFrom(Pair ...$values): Result;

    /**
     * 空のコレクションを作成する
     * @return static<TKey,TValue>
     */
    public static function empty(): static;
}
