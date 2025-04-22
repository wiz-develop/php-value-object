<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Number\Decimal;

use BCMath\Number;
use Override;
use WizDevelop\PhpValueObject\Number\Decimal\PositiveDecimalValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * PositiveDecimalValue抽象クラスのテスト用実装
 * ゼロを含まない正の小数値オブジェクト
 */
#[ValueObjectMeta(displayName: '正の数値')]
final readonly class TestPositiveDecimalValue extends PositiveDecimalValue
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
     * 最小値は0.01
     */
    #[Override]
    public static function min(): Number
    {
        return new Number('0.01');
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
     * ゼロを許容しない
     */
    #[Override]
    public static function includeZero(): bool
    {
        return false;
    }
}
