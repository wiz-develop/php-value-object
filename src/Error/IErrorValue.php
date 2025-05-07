<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Error;

use WizDevelop\PhpValueObject\IValueObject;

/**
 * エラー値オブジェクト インターフェース
 */
interface IErrorValue extends IValueObject
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
     * エラーをシリアライズする
     */
    public function serialize(): string;

    /**
     * シリアライズされたエラーをデシリアライズする
     *
     * @param  string $serialized シリアライズされたエラー
     * @return static デシリアライズされたエラー
     */
    public static function deserialize(string $serialized): static;
}
