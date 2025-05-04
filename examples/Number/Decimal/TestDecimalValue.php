<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Number\Decimal;

use BcMath\Number;
use Override;
use WizDevelop\PhpValueObject\Number\DecimalValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * DecimalValue抽象クラスのテスト用実装
 * 単にDecimalValueを実装するだけのシンプルなクラス
 */
#[ValueObjectMeta(displayName: '数値')]
final readonly class TestDecimalValue extends DecimalValue
{
    #[Override]
    protected static function scale(): int
    {
        return 2;
    }

    #[Override]
    protected static function min(): Number
    {
        return new Number('-1000');
    }

    #[Override]
    protected static function max(): Number
    {
        return new Number('1000');
    }
}
