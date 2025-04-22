<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number\Decimal;

use BCMath\Number;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\DecimalPrice;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\ZeroAllowedPositiveDecimal;
use WizDevelop\PhpValueObject\Number\Decimal\PositiveDecimalValue;
use WizDevelop\PhpValueObject\Number\NumberValueError;

#[TestDox('PositiveDecimalValueクラスのテスト')]
#[CoversClass(PositiveDecimalValue::class)]
#[CoversClass(DecimalPrice::class)]
#[CoversClass(ZeroAllowedPositiveDecimal::class)]
final class PositiveDecimalValueTest extends TestCase
{
    #[Test]
    public function 正の値でインスタンスが作成できる(): void
    {
        $value = DecimalPrice::from(new Number('100.50'));
        $this->assertEquals('100.50', (string)$value->value());
    }

    #[Test]
    public function 負の値ではエラーになる(): void
    {
        $result = DecimalPrice::tryFrom(new Number('-100.50'));
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
    }

    #[Test]
    public function includeZeroがfalseの場合にゼロ値はエラーになる(): void
    {
        // DecimalPrice は最小値が0であり、isPositiveでは0を許容しない
        $this->assertFalse(DecimalPrice::includeZero());
        $result = DecimalPrice::isPositive(new Number('0'));
        $this->assertFalse($result->isOk());

        // 正の値は許容される
        $result2 = DecimalPrice::isPositive(new Number('0.01'));
        $this->assertTrue($result2->isOk());
    }

    #[Test]
    public function includeZeroがtrueの場合にゼロ値は許容される(): void
    {
        // ZeroAllowedPositiveDecimal はゼロを許容する
        $this->assertTrue(ZeroAllowedPositiveDecimal::includeZero());

        // ゼロは許容される
        $result = ZeroAllowedPositiveDecimal::isPositive(new Number('0'));
        $this->assertTrue($result->isOk());

        // 正の値も許容される
        $result2 = ZeroAllowedPositiveDecimal::isPositive(new Number('0.01'));
        $this->assertTrue($result2->isOk());

        // 負の値は許容されない
        $result3 = ZeroAllowedPositiveDecimal::isPositive(new Number('-0.01'));
        $this->assertFalse($result3->isOk());
    }

    #[Test]
    public function 加算メソッドのテスト(): void
    {
        $value1 = DecimalPrice::from(new Number('10.5'));
        $value2 = DecimalPrice::from(new Number('20.3'));

        $result = $value1->tryAdd($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('30.80', (string)$result->unwrap()->value());
    }

    #[Test]
    public function 減算メソッドのテスト_正常系(): void
    {
        $value1 = DecimalPrice::from(new Number('30.5'));
        $value2 = DecimalPrice::from(new Number('10.3'));

        $result = $value1->trySub($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('20.20', (string)$result->unwrap()->value());
    }

    #[Test]
    public function 減算メソッドのテスト_結果が負になるとエラー(): void
    {
        $value1 = DecimalPrice::from(new Number('10.5'));
        $value2 = DecimalPrice::from(new Number('20.3'));

        $result = $value1->trySub($value2);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
    }

    #[Test]
    public function 乗算メソッドのテスト(): void
    {
        $value1 = DecimalPrice::from(new Number('10.5'));
        $value2 = DecimalPrice::from(new Number('2'));

        $result = $value1->tryMul($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('21.00', (string)$result->unwrap()->value());
    }

    #[Test]
    public function 除算メソッドのテスト(): void
    {
        $value1 = DecimalPrice::from(new Number('21'));
        $value2 = DecimalPrice::from(new Number('2'));

        $result = $value1->tryDiv($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('10.50', (string)$result->unwrap()->value());
    }

    #[Test]
    public function 比較メソッドのテスト(): void
    {
        $value1 = DecimalPrice::from(new Number('10.5'));
        $value2 = DecimalPrice::from(new Number('10.5'));
        $value3 = DecimalPrice::from(new Number('20.3'));

        // 等価比較
        $this->assertTrue($value1->equals($value2));
        $this->assertFalse($value1->equals($value3));

        // 大小比較
        $this->assertTrue($value1->lt($value3));  // less than
        $this->assertTrue($value1->lte($value3)); // less than or equal
        $this->assertTrue($value1->lte($value2)); // less than or equal (equal case)
        $this->assertFalse($value1->gt($value3)); // greater than
        $this->assertFalse($value1->gte($value3)); // greater than or equal
        $this->assertTrue($value1->gte($value2)); // greater than or equal (equal case)
    }

    #[Test]
    public function isZeroメソッドのテスト(): void
    {
        $value1 = DecimalPrice::from(new Number('0'));
        $value2 = DecimalPrice::from(new Number('10.5'));

        $this->assertTrue($value1->isZero());
        $this->assertFalse($value2->isZero());
    }
}
