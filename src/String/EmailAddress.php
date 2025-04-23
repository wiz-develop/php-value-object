<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\String;

use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * メールアドレスの値オブジェクト
 */
#[ValueObjectMeta(displayName: 'メールアドレス', description: 'メールアドレスの値オブジェクト')]
final readonly class EmailAddress extends StringValue
{
    /**
     * メールアドレスの最大文字数（RFC 5321に基づく）
     */
    private const int MIN_EMAIL_LENGTH = 1;
    private const int MAX_EMAIL_LENGTH = 254;

    /**
     * メールアドレスの正規表現パターン
     * RFC 5322に準拠した基本的な検証を行う
     */
    private const string EMAIL_REGEX = '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/';

    #[Override]
    public static function minLength(): int
    {
        return self::MIN_EMAIL_LENGTH;
    }

    #[Override]
    public static function maxLength(): int
    {
        return self::MAX_EMAIL_LENGTH;
    }

    #[Override]
    public static function regex(): string
    {
        return self::EMAIL_REGEX;
    }

    #[Override]
    public static function isValid(string $value): Result
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return Result\err(StringValueError::invalid(
                message: "「{$value}」は有効なメールアドレス形式ではありません。",
            ));
        }

        return Result\ok(true);
    }

    /**
     * メールアドレスのローカル部（@より前の部分）を取得
     */
    public function getLocalPart(): string
    {
        [$localPart, $_] = explode('@', $this->value, 2);

        return $localPart;
    }

    /**
     * メールアドレスのドメイン部（@より後の部分）を取得
     */
    public function getDomain(): string
    {
        [$_, $domain] = explode('@', $this->value, 2);

        return $domain;
    }
}
