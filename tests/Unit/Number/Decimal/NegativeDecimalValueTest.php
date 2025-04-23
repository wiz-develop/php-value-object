<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number\Decimal;

use BCMath\Number;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\TestNegativeDecimalValue;
use WizDevelop\PhpValueObject\Number\Decimal\NegativeDecimalValue;
use WizDevelop\PhpValueObject\Number\NumberValueError;
use WizDevelop\PhpValueObject\Tests\TestCase;

/**
 * NegativeDecimalValue抽象クラスのテスト
 */
#[TestDox('NegativeDecimalValue抽象クラスのテスト')]
#[CoversClass(NegativeDecimalValue::class)]
#[CoversClass(TestNegativeDecimalValue::class)]
final class NegativeDecimalValueTest extends TestCase
{
    #[Test]
    public function 負の値でインスタンスが作成できる(): void
    {
        $value = TestNegativeDecimalValue::from(new Number('-100.50'));
        $this->assertEquals('-100.50', (string)$value->value);
    }

    #[Test]
    public function 正の値ではエラーになる(): void
    {
        $result = TestNegativeDecimalValue::tryFrom(new Number('100.50'));
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // エラーメッセージに負の値であるべきというメッセージが含まれていることを確認
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('負の数', $errorMessage);
    }

    #[Test]
    public function 加算メソッドのテスト_正常系(): void
    {
        $value1 = TestNegativeDecimalValue::from(new Number('-30.5'));
        $value2 = TestNegativeDecimalValue::from(new Number('-20.3'));

        $result = $value1->tryAdd($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('-50.80', (string)$result->unwrap()->value);
    }

    #[Test]
    public function 加算メソッドのテスト_結果が範囲内に収まるケース(): void
    {
        $value1 = TestNegativeDecimalValue::from(new Number('-500'));
        $value2 = TestNegativeDecimalValue::from(new Number('-100'));

        // -500 + (-100) = -600（範囲内）
        $result = $value1->tryAdd($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('-600.00', (string)$result->unwrap()->value);
    }

    #[Test]
    public function 減算メソッドのテスト_正常系(): void
    {
        // -30.5 - (-10.5) = -30.5 + 10.5 = -20
        $value1 = TestNegativeDecimalValue::from(new Number('-30.5'));
        $value2 = TestNegativeDecimalValue::from(new Number('-10.5'));

        $result = $value1->trySub($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('-20.00', (string)$result->unwrap()->value);
    }

    #[Test]
    public function 比較メソッドのテスト(): void
    {
        $value1 = TestNegativeDecimalValue::from(new Number('-10.5'));
        $value2 = TestNegativeDecimalValue::from(new Number('-10.5'));
        $value3 = TestNegativeDecimalValue::from(new Number('-20.3'));

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
    public function 範囲外の値はエラーになる(): void
    {
        // 最小値未満
        $result1 = TestNegativeDecimalValue::tryFrom(new Number('-1000.01'));
        $this->assertFalse($result1->isOk());

        // 最大値超過
        $result2 = TestNegativeDecimalValue::tryFrom(new Number('0'));
        $this->assertFalse($result2->isOk());

        // 範囲内
        $result3 = TestNegativeDecimalValue::tryFrom(new Number('-1000'));
        $this->assertTrue($result3->isOk());

        $result4 = TestNegativeDecimalValue::tryFrom(new Number('-0.01'));
        $this->assertTrue($result4->isOk());
    }

    #[Test]
    public function スケールオーバーの値はエラーになる(): void
    {
        $result = TestNegativeDecimalValue::tryFrom(new Number('-100.123'));
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // エラーメッセージにスケール情報が含まれていることを確認
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('2', $errorMessage); // 許容スケール
        $this->assertStringContainsString('3', $errorMessage); // 実際のスケール
    }
}
