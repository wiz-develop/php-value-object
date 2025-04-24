<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Number\Integer;

use Override;
use WizDevelop\PhpValueObject\Number\PositiveIntegerValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * PositiveIntegerValue抽象クラスのテスト用実装
 * 単にPositiveIntegerValueを実装するだけのシンプルなクラス
 */
#[ValueObjectMeta(displayName: '正の整数')]
final readonly class TestPositiveIntegerValue extends PositiveIntegerValue
{
    #[Override]
    protected static function max(): int
    {
        return 1000;
    }
}
