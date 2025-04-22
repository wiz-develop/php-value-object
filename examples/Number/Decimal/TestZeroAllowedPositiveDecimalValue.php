<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Number\Decimal;

use BCMath\Number;
use Override;
use WizDevelop\PhpValueObject\Number\Decimal\PositiveDecimalValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * PositiveDecimalValue抽象クラスのテスト用実装
 * ゼロを許容する正の小数値オブジェクト
 */
#[ValueObjectMeta(displayName: 'ゼロ許容正の数値')]
final readonly class TestZeroAllowedPositiveDecimalValue extends PositiveDecimalValue
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
     * 最大値は1000
     */
    #[Override]
    public static function max(): Number
    {
        return new Number('1000');
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
