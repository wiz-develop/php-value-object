<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\String;

use Override;
use WizDevelop\PhpValueObject\String\StringValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * 文字列の値オブジェクトの例
 */
#[ValueObjectMeta(displayName: '文字列', description: '文字列の値オブジェクトの例')]
final readonly class TestStringValue extends StringValue
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
