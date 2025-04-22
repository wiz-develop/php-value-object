<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Number\Decimal;

use BCMath\Number;
use Override;
use WizDevelop\PhpValueObject\Number\Decimal\PositiveDecimalValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * 金額を表す小数値オブジェクト
 */
#[ValueObjectMeta(displayName: '金額')]
final readonly class DecimalPrice extends PositiveDecimalValue
{
    /**
     * 小数点以下2桁まで許容する（通貨の表現に対応）
     */
    #[Override]
    public static function scale(): int
    {
        return 2;
    }

    /**
     * 最小値は0
     */
    #[Override]
    public static function min(): Number
    {
        return new Number('0');
    }

    /**
     * 最大値は1,000,000（100万）
     */
    #[Override]
    public static function max(): Number
    {
        return new Number('1000000');
    }
}
