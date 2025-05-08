<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Number\Decimal;

use BcMath\Number;
use Override;
use WizDevelop\PhpValueObject\Number\NegativeDecimalValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * NegativeDecimalValue抽象クラスのテスト用実装
 * ゼロを含まない負の小数値オブジェクト
 */
#[ValueObjectMeta(name: '負の数値')]
final readonly class TestNegativeDecimalValue extends NegativeDecimalValue
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
        return new Number('-0.01');
    }
}
