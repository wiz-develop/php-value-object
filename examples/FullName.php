<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples;

use Override;
use WizDevelop\PhpValueObject\String\StringValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * 氏名を表す値オブジェクト
 */
#[ValueObjectMeta(displayName: '氏名', description: 'ユーザーの氏名を表します')]
final readonly class FullName extends StringValue
{
    #[Override]
    public static function minLength(): int
    {
        return 1;
    }

    #[Override]
    public static function maxLength(): int
    {
        return 50;
    }

    #[Override]
    public static function regex(): string
    {
        return '/^[\p{L}\p{N}\s\'"-]+$/u';  // 文字、数字、空白、一部の記号を許可
    }
}
