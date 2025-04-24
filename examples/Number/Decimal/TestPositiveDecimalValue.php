<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Number\Decimal;

use BCMath\Number;
use Override;
use WizDevelop\PhpValueObject\Number\PositiveDecimalValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * PositiveDecimalValue抽象クラスのテスト用実装
 * ゼロを含まない正の小数値オブジェクト
 */
#[ValueObjectMeta(displayName: '正の数値')]
final readonly class TestPositiveDecimalValue extends PositiveDecimalValue
{
    #[Override]
    protected static function scale(): int
    {
        return 2;
    }

    #[Override]
    protected static function min(): Number
    {
        return new Number('0.01');
    }

    #[Override]
    protected static function max(): Number
    {
        return new Number('1000');
    }
}
