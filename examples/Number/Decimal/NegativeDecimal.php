<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Number\Decimal;

use BCMath\Number;
use Override;
use WizDevelop\PhpValueObject\Number\Decimal\NegativeDecimalValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * 負の小数値オブジェクト（ゼロを含まない）
 */
#[ValueObjectMeta(displayName: '負数')]
final readonly class NegativeDecimal extends NegativeDecimalValue
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
     * 最大値は-0.01（ゼロは含まない）
     */
    #[Override]
    public static function max(): Number
    {
        return new Number('-0.01');
    }

    /**
     * ゼロを許容しない（falseを返す）
     */
    #[Override]
    public static function includeZero(): bool
    {
        return false;
    }
}
