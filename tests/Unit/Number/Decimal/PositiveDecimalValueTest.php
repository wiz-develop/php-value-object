<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number\Decimal;

use BCMath\Number;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\TestPositiveDecimalValue;
use WizDevelop\PhpValueObject\Number\NumberValueError;
use WizDevelop\PhpValueObject\Number\PositiveDecimalValue;
use WizDevelop\PhpValueObject\Tests\TestCase;

/**
 * PositiveDecimalValue抽象クラスのテスト
 */
#[TestDox('PositiveDecimalValue抽象クラスのテスト')]
#[CoversClass(PositiveDecimalValue::class)]
#[CoversClass(TestPositiveDecimalValue::class)]
final class PositiveDecimalValueTest extends TestCase
{
    #[Test]
    public function 正の値でインスタンスが作成できる(): void
    {
        $value = TestPositiveDecimalValue::from(new Number('100.50'));
        $this->assertEquals('100.50', (string)$value->value);
    }

    #[Test]
    public function 負の値ではエラーになる(): void
    {
        $result = TestPositiveDecimalValue::tryFrom(new Number('-100.50'));
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // エラーメッセージに正の値であるべきというメッセージが含まれていることを確認
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('正の数', $errorMessage);
    }

    #[Test]
    public function 加算で結果が範囲外になる場合はエラーになる(): void
    {
        $value1 = TestPositiveDecimalValue::from(new Number('900'));
        $value2 = TestPositiveDecimalValue::from(new Number('200'));

        // 900 + 200 = 1100（最大値1000を超えるのでエラー）
        $result = $value1->tryAdd($value2);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
    }

    #[Test]
    public function 減算で結果が負になる場合はエラーになる(): void
    {
        $value1 = TestPositiveDecimalValue::from(new Number('10.5'));
        $value2 = TestPositiveDecimalValue::from(new Number('20.5'));

        // 10.5 - 20.5 = -10.0（負の値になるのでエラー）
        $result = $value1->trySub($value2);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
    }

    #[Test]
    public function 乗算で結果が範囲外になる場合はエラーになる(): void
    {
        $value1 = TestPositiveDecimalValue::from(new Number('500'));
        $value2 = TestPositiveDecimalValue::from(new Number('3'));

        // 500 * 3 = 1500（最大値1000を超えるのでエラー）
        $result = $value1->tryMul($value2);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
    }

    #[Test]
    public function 範囲外の値はエラーになる(): void
    {
        // 最小値未満
        $result1 = TestPositiveDecimalValue::tryFrom(new Number('0'));
        $this->assertFalse($result1->isOk());

        // 最大値超過
        $result2 = TestPositiveDecimalValue::tryFrom(new Number('1000.01'));
        $this->assertFalse($result2->isOk());

        // 範囲内
        $result3 = TestPositiveDecimalValue::tryFrom(new Number('0.01'));
        $this->assertTrue($result3->isOk());

        $result4 = TestPositiveDecimalValue::tryFrom(new Number('1000'));
        $this->assertTrue($result4->isOk());
    }

    #[Test]
    public function スケールオーバーの値はエラーになる(): void
    {
        $result = TestPositiveDecimalValue::tryFrom(new Number('100.123'));
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // エラーメッセージにスケール情報が含まれていることを確認
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('2', $errorMessage); // 許容スケール
        $this->assertStringContainsString('3', $errorMessage); // 実際のスケール
    }
}
