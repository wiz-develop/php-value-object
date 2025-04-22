<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Number\Decimal;

use BCMath\Number;
use Override;
use WizDevelop\PhpValueObject\Number\Decimal\DecimalValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * DecimalValue抽象クラスのテスト用実装
 * 単にDecimalValueを実装するだけのシンプルなクラス
 */
#[ValueObjectMeta(displayName: '数値')]
final readonly class TestDecimalValue extends DecimalValue
{
    /**
     * 小数点以下2桁まで許容する
     */
    #[Override]
    public static function scale(): int
    {
        return 2;
    }

    /**
     * 最小値は-1000
     */
    #[Override]
    public static function min(): Number
    {
        return new Number('-1000');
    }

    /**
     * 最大値は1000
     */
    #[Override]
    public static function max(): Number
    {
        return new Number('1000');
    }
}
