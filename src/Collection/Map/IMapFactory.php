<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection\Map;

use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Collection\Pair;
use WizDevelop\PhpValueObject\Error\IErrorValue;
use WizDevelop\PhpValueObject\Error\ValueObjectError;

/**
 * マップコレクション ファクトリインターフェース
 * @see WizDevelop\PhpValueObject\Collection\Map
 *
 * @template TKey
 * @template TValue
 */
interface IMapFactory
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
     * @param  Pair<TTryFromKey,TTryFromValue>                            ...$values
     * @return Result<static<TTryFromKey,TTryFromValue>,ValueObjectError>
     */
    public static function tryFrom(Pair ...$values): Result;

    /**
     * 信頼できるプリミティブ値からインスタンスを生成する
     *
     * @template TTryFromKey of TKey
     * @template TTryFromValue of TValue
     *
     * @param  (Pair<Result<TTryFromKey,IErrorValue>,Result<TTryFromValue,IErrorValue>>|Pair) ...$values
     * @return Result<static<TTryFromKey,TTryFromValue>,ValueObjectError>
     */
    public static function tryFromResults(Pair ...$values): Result; /** @phpstan-ignore-line */

    /**
     * 空のコレクションを作成する
     * @return static<TKey,TValue>
     */
    public static function empty(): static;
}
