<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Number\Integer;

use Override;
use WizDevelop\PhpValueObject\Number\Integer\NegativeIntegerValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * NegativeIntegerValue抽象クラスのテスト用実装
 * 単にNegativeIntegerValueを実装するだけのシンプルなクラス
 */
#[ValueObjectMeta(displayName: '負の整数')]
final readonly class TestNegativeIntegerValue extends NegativeIntegerValue
{
    #[Override]
    protected static function min(): int
    {
        return -1000;
    }
}
