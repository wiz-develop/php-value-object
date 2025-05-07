<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection\List;

use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;

/**
 * リストコレクション ファクトリインターフェース
 * @see WizDevelop\PhpValueObject\Collection\ArrayList
 *
 * @template TValue
 */
interface IArrayListFactory
{
    /**
     * 信頼できるプリミティブ値からインスタンスを生成する
     *
     * @template TFromValue of TValue
     *
     * @param  array<int,TFromValue> $elements
     * @return static<TFromValue>
     */
    public static function from(array $elements): static;

    /**
     * 信頼できないプリミティブ値からインスタンスを生成する
     *
     * @template TTryFromValue of TValue
     *
     * @param  array<int,TTryFromValue>                       $elements
     * @return Result<static<TTryFromValue>,ValueObjectError>
     */
    public static function tryFrom(array $elements): Result;

    /**
     * 空のコレクションを作成する
     * @return static<TValue>
     */
    public static function empty(): static;
}
