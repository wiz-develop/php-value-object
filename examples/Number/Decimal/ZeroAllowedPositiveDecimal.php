<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Number\Decimal;

use BCMath\Number;
use Override;
use WizDevelop\PhpValueObject\Number\Decimal\PositiveDecimalValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * ゼロを許容する正の小数値オブジェクト
 */
#[ValueObjectMeta(displayName: 'ゼロ許容正数')]
final readonly class ZeroAllowedPositiveDecimal extends PositiveDecimalValue
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
     * 最小値は0
     */
    #[Override]
    public static function min(): Number
    {
        return new Number('0');
    }

    /**
     * 最大値は1,000
     */
    #[Override]
    public static function max(): Number
    {
        return new Number('1000');
    }

    /**
     * ゼロを許容する（trueを返す）
     */
    #[Override]
    public static function includeZero(): bool
    {
        return true;
    }
}
