<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number\Decimal;

use BCMath\Number;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\DecimalPrice;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\NegativeDecimal;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\ZeroAllowedNegativeDecimal;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\ZeroAllowedPositiveDecimal;
use WizDevelop\PhpValueObject\Number\Decimal\DecimalValue;
use WizDevelop\PhpValueObject\Number\NumberValueError;

#[TestDox('DecimalValue抽象クラスの共通機能テスト')]
#[CoversClass(DecimalValue::class)]
#[CoversClass(DecimalPrice::class)]
#[CoversClass(NegativeDecimal::class)]
#[CoversClass(ZeroAllowedPositiveDecimal::class)]
#[CoversClass(ZeroAllowedNegativeDecimal::class)]
final class DecimalValueCommonTest extends TestCase
{
    #[Test]
    public function value関数で内部値を取得できる(): void
    {
        $price = DecimalPrice::from(new Number('123.45'));
        $this->assertEquals('123.45', (string)$price->value());

        $negative = NegativeDecimal::from(new Number('-123.45'));
        $this->assertEquals('-123.45', (string)$negative->value());
    }

    #[Test]
    public function isZero関数でゼロかどうかを判定できる(): void
    {
        // 正の値でゼロを許容するクラス
        $zeroPositive = ZeroAllowedPositiveDecimal::from(new Number('0'));
        $nonZeroPositive = ZeroAllowedPositiveDecimal::from(new Number('1.23'));
        $this->assertTrue($zeroPositive->isZero());
        $this->assertFalse($nonZeroPositive->isZero());

        // 負の値でゼロを許容するクラス
        $zeroNegative = ZeroAllowedNegativeDecimal::from(new Number('0'));
        $nonZeroNegative = ZeroAllowedNegativeDecimal::from(new Number('-1.23'));
        $this->assertTrue($zeroNegative->isZero());
        $this->assertFalse($nonZeroNegative->isZero());
    }

    #[Test]
    public function fromNullable関数でNullを扱える(): void
    {
        // Null値の場合
        $option1 = DecimalPrice::fromNullable(null);
        $this->assertTrue($option1->isNone());

        // 非Null値の場合
        $option2 = DecimalPrice::fromNullable(new Number('123.45'));
        $this->assertTrue($option2->isSome());
        $this->assertEquals('123.45', (string)$option2->unwrap()->value());
    }

    #[Test]
    public function tryFromNullable関数でNullを扱える(): void
    {
        // Null値の場合
        $result1 = DecimalPrice::tryFromNullable(null);
        $this->assertTrue($result1->isOk());
        $this->assertTrue($result1->unwrap()->isNone());

        // 有効な非Null値の場合
        $result2 = DecimalPrice::tryFromNullable(new Number('123.45'));
        $this->assertTrue($result2->isOk());
        $this->assertTrue($result2->unwrap()->isSome());
        $this->assertEquals('123.45', (string)$result2->unwrap()->unwrap()->value());

        // 無効な非Null値の場合
        $result3 = DecimalPrice::tryFromNullable(new Number('-123.45')); // 負の値は無効
        $this->assertFalse($result3->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result3->unwrapErr());
    }

    #[Test]
    public function scale関数でスケールを取得できる(): void
    {
        $this->assertEquals(2, DecimalPrice::scale());
        $this->assertEquals(2, NegativeDecimal::scale());
    }

    #[Test]
    public function min関数とmax関数で最小値と最大値を取得できる(): void
    {
        // DecimalPrice
        $min1 = DecimalPrice::min();
        $max1 = DecimalPrice::max();
        $this->assertEquals('0', (string)$min1);
        $this->assertEquals('1000000', (string)$max1);

        // NegativeDecimal
        $min2 = NegativeDecimal::min();
        $max2 = NegativeDecimal::max();
        $this->assertEquals('-1000', (string)$min2);
        $this->assertEquals('-0.01', (string)$max2);
    }

    #[Test]
    public function isScaleValid関数でスケールの妥当性をチェックできる(): void
    {
        // 有効なスケール
        $result1 = DecimalPrice::isScaleValid(new Number('123.45'));
        $this->assertTrue($result1->isOk());

        // 無効なスケール
        $result2 = DecimalPrice::isScaleValid(new Number('123.456'));
        $this->assertFalse($result2->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result2->unwrapErr());

        // エラーメッセージにスケール情報が含まれていることを確認
        $errorMessage = $result2->unwrapErr()->getMessage();
        $this->assertStringContainsString('2', $errorMessage);
        $this->assertStringContainsString('3', $errorMessage);
    }

    #[Test]
    public function isRangeValid関数で範囲の妥当性をチェックできる(): void
    {
        // 有効な範囲
        $result1 = DecimalPrice::isRangeValid(new Number('500'));
        $this->assertTrue($result1->isOk());

        // 範囲外（下限）
        $result2 = DecimalPrice::isRangeValid(new Number('-0.01'));
        $this->assertFalse($result2->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result2->unwrapErr());

        // 範囲外（上限）
        $result3 = DecimalPrice::isRangeValid(new Number('1000000.01'));
        $this->assertFalse($result3->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result3->unwrapErr());

        // エラーメッセージに範囲情報が含まれていることを確認
        $errorMessage = $result3->unwrapErr()->getMessage();
        $this->assertStringContainsString('0', $errorMessage);
        $this->assertStringContainsString('1000000', $errorMessage);
    }

    #[Test]
    public function tryFrom関数で有効な値を検証してインスタンス化できる(): void
    {
        // 有効な値
        $result1 = DecimalPrice::tryFrom(new Number('123.45'));
        $this->assertTrue($result1->isOk());
        $this->assertEquals('123.45', (string)$result1->unwrap()->value());

        // 無効な値（負の値）
        $result2 = DecimalPrice::tryFrom(new Number('-123.45'));
        $this->assertFalse($result2->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result2->unwrapErr());

        // 無効な値（スケールオーバー）
        $result3 = DecimalPrice::tryFrom(new Number('123.456'));
        $this->assertFalse($result3->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result3->unwrapErr());

        // 無効な値（範囲外）
        $result4 = DecimalPrice::tryFrom(new Number('1000000.01'));
        $this->assertFalse($result4->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result4->unwrapErr());
    }
}
