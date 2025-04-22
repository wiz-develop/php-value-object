<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number\Decimal;

use BCMath\Number;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\NegativeDecimal;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\ZeroAllowedNegativeDecimal;
use WizDevelop\PhpValueObject\Number\Decimal\NegativeDecimalValue;
use WizDevelop\PhpValueObject\Number\NumberValueError;

#[TestDox('NegativeDecimalValueクラスのテスト')]
#[CoversClass(NegativeDecimalValue::class)]
#[CoversClass(NegativeDecimal::class)]
#[CoversClass(ZeroAllowedNegativeDecimal::class)]
final class NegativeDecimalValueTest extends TestCase
{
    #[Test]
    public function 負の値でインスタンスが作成できる(): void
    {
        $value = NegativeDecimal::from(new Number('-100.50'));
        $this->assertEquals('-100.50', (string)$value->value());
    }

    #[Test]
    public function 正の値ではエラーになる(): void
    {
        $result = NegativeDecimal::tryFrom(new Number('100.50'));
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
    }

    #[Test]
    public function includeZeroがfalseの場合にゼロ値はエラーになる(): void
    {
        // NegativeDecimal はゼロを許容しない
        $this->assertFalse(NegativeDecimal::includeZero());

        // ゼロは許容されない
        $result = NegativeDecimal::isNegative(new Number('0'));
        $this->assertFalse($result->isOk());

        // 負の値は許容される
        $result2 = NegativeDecimal::isNegative(new Number('-0.01'));
        $this->assertTrue($result2->isOk());
    }

    #[Test]
    public function includeZeroがtrueの場合にゼロ値は許容される(): void
    {
        // ZeroAllowedNegativeDecimal はゼロを許容する
        $this->assertTrue(ZeroAllowedNegativeDecimal::includeZero());

        // ゼロは許容される
        $result = ZeroAllowedNegativeDecimal::isNegative(new Number('0'));
        $this->assertTrue($result->isOk());

        // 負の値も許容される
        $result2 = ZeroAllowedNegativeDecimal::isNegative(new Number('-0.01'));
        $this->assertTrue($result2->isOk());

        // 正の値は許容されない
        $result3 = ZeroAllowedNegativeDecimal::isNegative(new Number('0.01'));
        $this->assertFalse($result3->isOk());
    }

    #[Test]
    public function 加算メソッドのテスト_正常系(): void
    {
        $value1 = NegativeDecimal::from(new Number('-30.5'));
        $value2 = NegativeDecimal::from(new Number('-20.3'));

        $result = $value1->tryAdd($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('-50.80', (string)$result->unwrap()->value());
    }

    #[Test]
    public function 加算メソッドのテスト_結果が負でなくなるとエラー(): void
    {
        $value1 = NegativeDecimal::from(new Number('-10.5'));
        $value2 = NegativeDecimal::from(new Number('-10.5'));

        // 負の数同士を足して0になる場合はエラー
        $result = $value1->tryAdd($value2);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
    }

    #[Test]
    public function 減算メソッドのテスト(): void
    {
        $value1 = NegativeDecimal::from(new Number('-10.5'));
        $value2 = NegativeDecimal::from(new Number('-30.5'));

        // -10.5 - (-30.5) = -10.5 + 30.5 = 20 => エラー（負の数じゃなくなる）
        $result = $value1->trySub($value2);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // 引く数の方が小さい場合は正常に減算できる
        $value3 = NegativeDecimal::from(new Number('-30.5'));
        $value4 = NegativeDecimal::from(new Number('-10.5'));

        // -30.5 - (-10.5) = -30.5 + 10.5 = -20
        $result2 = $value3->trySub($value4);
        $this->assertTrue($result2->isOk());
        $this->assertEquals('-20.00', (string)$result2->unwrap()->value());
    }

    #[Test]
    public function 乗算メソッドのテスト(): void
    {
        $value1 = NegativeDecimal::from(new Number('-10.5'));
        $value2 = NegativeDecimal::from(new Number('-2'));

        // 負の数 × 負の数 = 正の数 => エラー
        $result = $value1->tryMul($value2);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // 負の数 × 正の数（正確には負の数の絶対値） = 負の数
        $result2 = $value1->tryMul(NegativeDecimal::from(new Number('2')));
        $this->assertTrue($result2->isOk());
        $this->assertEquals('-21.00', (string)$result2->unwrap()->value());
    }

    #[Test]
    public function 除算メソッドのテスト(): void
    {
        $value1 = NegativeDecimal::from(new Number('-21'));
        $value2 = NegativeDecimal::from(new Number('-2'));

        // 負の数 ÷ 負の数 = 正の数 => エラー
        $result = $value1->tryDiv($value2);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // 負の数 ÷ 正の数（正確には負の数の絶対値） = 負の数
        $result2 = $value1->tryDiv(NegativeDecimal::from(new Number('2')));
        $this->assertTrue($result2->isOk());
        $this->assertEquals('-10.50', (string)$result2->unwrap()->value());
    }

    #[Test]
    public function 比較メソッドのテスト(): void
    {
        $value1 = NegativeDecimal::from(new Number('-10.5'));
        $value2 = NegativeDecimal::from(new Number('-10.5'));
        $value3 = NegativeDecimal::from(new Number('-20.3'));

        // 等価比較
        $this->assertTrue($value1->equals($value2));
        $this->assertFalse($value1->equals($value3));

        // 大小比較（負の数の場合、数値が小さいほど大きい値）
        $this->assertTrue($value1->gt($value3));  // greater than: -10.5 > -20.3
        $this->assertTrue($value1->gte($value3)); // greater than or equal
        $this->assertTrue($value1->gte($value2)); // greater than or equal (equal case)
        $this->assertFalse($value1->lt($value3)); // less than
        $this->assertFalse($value1->lte($value3)); // less than or equal
        $this->assertTrue($value1->lte($value2)); // less than or equal (equal case)
    }

    #[Test]
    public function isZeroメソッドのテスト(): void
    {
        // ゼロを許容するクラスでテスト
        $value1 = ZeroAllowedNegativeDecimal::from(new Number('0'));
        $value2 = ZeroAllowedNegativeDecimal::from(new Number('-10.5'));

        $this->assertTrue($value1->isZero());
        $this->assertFalse($value2->isZero());
    }

    #[Test]
    public function 有効範囲のテスト(): void
    {
        // 範囲内の値
        $result1 = NegativeDecimal::tryFrom(new Number('-500'));
        $this->assertTrue($result1->isOk());

        // 最小値
        $result2 = NegativeDecimal::tryFrom(new Number('-1000'));
        $this->assertTrue($result2->isOk());

        // 最大値
        $result3 = NegativeDecimal::tryFrom(new Number('-0.01'));
        $this->assertTrue($result3->isOk());

        // 範囲外（最小値を下回る）
        $result4 = NegativeDecimal::tryFrom(new Number('-1000.01'));
        $this->assertFalse($result4->isOk());

        // 範囲外（最大値を上回る）
        $result5 = NegativeDecimal::tryFrom(new Number('0'));
        $this->assertFalse($result5->isOk());
    }

    #[Test]
    public function スケールのテスト(): void
    {
        // 有効なスケール
        $result1 = NegativeDecimal::tryFrom(new Number('-123.45'));
        $this->assertTrue($result1->isOk());

        // スケールオーバー
        $result2 = NegativeDecimal::tryFrom(new Number('-123.456'));
        $this->assertFalse($result2->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result2->unwrapErr());
    }
}
