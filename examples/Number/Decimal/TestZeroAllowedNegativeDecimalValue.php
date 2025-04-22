<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Number\Decimal;

use BCMath\Number;
use Override;
use WizDevelop\PhpValueObject\Number\Decimal\NegativeDecimalValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * NegativeDecimalValue抽象クラスのテスト用実装
 * ゼロを許容する負の小数値オブジェクト
 */
#[ValueObjectMeta(displayName: 'ゼロ許容負の数値')]
final readonly class TestZeroAllowedNegativeDecimalValue extends NegativeDecimalValue
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
     * ゼロを許容する
     */
    #[Override]
    public static function includeZero(): bool
    {
        return true;
    }
}
