<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Error;

use Stringable;
use WizDevelop\PhpValueObject\IValueObject;

/**
 * エラー値オブジェクト インターフェース
 */
interface IErrorValue extends IValueObject, Stringable
{
    /**
     * シリアライズする際の区切り文字列
     * @var non-empty-string
     */
    public const string SEPARATOR = '||';

    /**
     * エラーコードを取得する
     */
    public function getCode(): string;

    /**
     * エラーメッセージを取得する
     */
    public function getMessage(): string;

    /**
     * エラーの詳細を取得する
     *
     * @return IErrorValue[] エラーの詳細
     */
    public function getDetails(): array;

    /**
     * エラーをシリアライズする
     */
    public function serialize(): string;

    /**
     * シリアライズされたエラーをデシリアライズする
     *
     * @param  string      $serialized シリアライズされたエラー
     * @return IErrorValue デシリアライズされたエラー
     */
    public static function deserialize(string $serialized): self;
}
