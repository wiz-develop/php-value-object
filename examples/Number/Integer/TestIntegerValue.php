<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Number\Integer;

use Override;
use WizDevelop\PhpValueObject\Number\Integer\IntegerValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * IntegerValue抽象クラスのテスト用実装
 * 単にIntegerValueを実装するだけのシンプルなクラス
 */
#[ValueObjectMeta(displayName: '整数')]
final readonly class TestIntegerValue extends IntegerValue
{
    /**
     * 最小値は-1000
     */
    #[Override]
    public static function min(): int
    {
        return -1000;
    }

    /**
     * 最大値は1000
     */
    #[Override]
    public static function max(): int
    {
        return 1000;
    }
}
