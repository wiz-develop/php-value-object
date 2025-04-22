<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Number\Integer;

use Override;
use WizDevelop\PhpValueObject\Number\Integer\PositiveIntegerValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * PositiveIntegerValue抽象クラスのテスト用実装
 * 単にPositiveIntegerValueを実装するだけのシンプルなクラス
 */
#[ValueObjectMeta(displayName: '正の整数')]
final readonly class TestPositiveIntegerValue extends PositiveIntegerValue
{
    /**
     * 最大値は1000
     */
    #[Override]
    public static function max(): int
    {
        return 1000;
    }

    /**
     * ゼロを許容しない
     */
    #[Override]
    public static function includeZero(): bool
    {
        return false;
    }
}
