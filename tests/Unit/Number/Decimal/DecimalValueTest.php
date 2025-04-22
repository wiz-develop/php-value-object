<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number\Decimal;

use BCMath\Number;
use DivisionByZeroError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\TestDecimalValue;
use WizDevelop\PhpValueObject\Number\Decimal\DecimalValue;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * DecimalValue抽象クラスの実装テスト
 */
#[TestDox('DecimalValue抽象クラスの実装テスト')]
#[CoversClass(DecimalValue::class)]
#[CoversClass(TestDecimalValue::class)]
final class DecimalValueTest extends TestCase
{
    #[Test]
    public function 有効な値でインスタンスが作成できる(): void
    {
        $decimal = TestDecimalValue::from(new Number('100.50'));
        $this->assertEquals('100.50', (string)$decimal->value());
    }

    #[Test]
    public function 最小値でインスタンスが作成できる(): void
    {
        $decimal = TestDecimalValue::from(new Number('-1000'));
        $this->assertEquals('-1000', (string)$decimal->value());
    }

    #[Test]
    public function 最大値でインスタンスが作成できる(): void
    {
        $decimal = TestDecimalValue::from(new Number('1000'));
        $this->assertEquals('1000', (string)$decimal->value());
    }

    #[Test]
    public function 範囲外の値はエラーになる(): void
    {
        // 最小値未満
        $result1 = TestDecimalValue::tryFrom(new Number('-1001'));
        $this->assertFalse($result1->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result1->unwrapErr());

        // 最大値超過
        $result2 = TestDecimalValue::tryFrom(new Number('1001'));
        $this->assertFalse($result2->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result2->unwrapErr());

        // エラーメッセージに範囲情報が含まれているか確認
        $errorMessage = $result2->unwrapErr()->getMessage();
        $this->assertStringContainsString('-1000', $errorMessage); // 最小値
        $this->assertStringContainsString('1000', $errorMessage);  // 最大値
        $this->assertStringContainsString('1001', $errorMessage);  // 入力値
    }

    #[Test]
    public function スケールオーバーの値はエラーになる(): void
    {
        $result = TestDecimalValue::tryFrom(new Number('100.123'));
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // エラーメッセージにスケール情報が含まれているか確認
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('2', $errorMessage); // 許容スケール
        $this->assertStringContainsString('3', $errorMessage); // 実際のスケール
    }

    /**
     * @return array<string, array{string}>
     */
    public static function 有効な値のパターンを提供(): array
    {
        return [
            '整数_正' => ['100'],
            '整数_負' => ['-100'],
            '小数点以下1桁_正' => ['100.5'],
            '小数点以下1桁_負' => ['-100.5'],
            '小数点以下2桁_正' => ['100.50'],
            '小数点以下2桁_負' => ['-100.50'],
            'ゼロ' => ['0'],
            '最小値' => ['-1000'],
            '最大値' => ['1000'],
        ];
    }

    #[Test]
    #[DataProvider('有効な値のパターンを提供')]
    public function 有効な値はインスタンスが作成できる(string $validValue): void
    {
        $result = TestDecimalValue::tryFrom(new Number($validValue));
        $this->assertTrue($result->isOk());
        $this->assertEquals($validValue, (string)$result->unwrap()->value());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function 無効な値のパターンを提供(): array
    {
        return [
            '最小値未満' => ['-1000.01'],
            '最大値超過' => ['1000.01'],
            '小数点以下が多すぎる' => ['100.123'],
        ];
    }

    #[Test]
    #[DataProvider('無効な値のパターンを提供')]
    public function 無効な値はエラーになる(string $invalidValue): void
    {
        $result = TestDecimalValue::tryFrom(new Number($invalidValue));
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
    }

    #[Test]
    public function 算術演算子のテスト_加算(): void
    {
        $value1 = TestDecimalValue::from(new Number('100.50'));
        $value2 = TestDecimalValue::from(new Number('200.25'));

        $result = $value1->tryAdd($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('300.75', (string)$result->unwrap()->value());
    }

    #[Test]
    public function 算術演算子のテスト_減算(): void
    {
        $value1 = TestDecimalValue::from(new Number('200.50'));
        $value2 = TestDecimalValue::from(new Number('100.25'));

        $result = $value1->trySub($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('100.25', (string)$result->unwrap()->value());
    }

    #[Test]
    public function 算術演算子のテスト_乗算(): void
    {
        $value = TestDecimalValue::from(new Number('100.50'));
        $multiplier = TestDecimalValue::from(new Number('2'));

        $result = $value->tryMul($multiplier);
        $this->assertTrue($result->isOk());
        $this->assertEquals('201.00', (string)$result->unwrap()->value());
    }

    #[Test]
    public function 算術演算子のテスト_除算(): void
    {
        $value = TestDecimalValue::from(new Number('100.50'));
        $divisor = TestDecimalValue::from(new Number('2'));

        $result = $value->tryDiv($divisor);
        $this->assertTrue($result->isOk());
        $this->assertEquals('50.25', (string)$result->unwrap()->value());
    }

    #[Test]
    public function 算術演算子のテスト_除算_ゼロ除算(): void
    {
        $value = TestDecimalValue::from(new Number('100.50'));
        $divisor = TestDecimalValue::from(new Number('0'));

        // tryDiv（例外を投げない）
        $result = $value->tryDiv($divisor);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // div（例外を投げる）
        $this->expectException(DivisionByZeroError::class);
        $value->div($divisor);
    }

    #[Test]
    public function 比較演算子のテスト_等価比較(): void
    {
        $value1 = TestDecimalValue::from(new Number('100.50'));
        $value2 = TestDecimalValue::from(new Number('100.50'));
        $value3 = TestDecimalValue::from(new Number('200.75'));

        $this->assertTrue($value1->equals($value2));
        $this->assertFalse($value1->equals($value3));
    }

    #[Test]
    public function 比較演算子のテスト_大小比較(): void
    {
        $value1 = TestDecimalValue::from(new Number('100.50'));
        $value2 = TestDecimalValue::from(new Number('200.75'));

        $this->assertTrue($value1->lt($value2));
        $this->assertTrue($value1->lte($value2));
        $this->assertFalse($value1->gt($value2));
        $this->assertFalse($value1->gte($value2));

        $this->assertTrue($value2->gt($value1));
        $this->assertTrue($value2->gte($value1));
        $this->assertFalse($value2->lt($value1));
        $this->assertFalse($value2->lte($value1));
    }

    #[Test]
    public function NullableメソッドでNullを扱える(): void
    {
        $option = TestDecimalValue::fromNullable(null);
        $this->assertTrue($option->isNone());

        $result = TestDecimalValue::tryFromNullable(null);
        $this->assertTrue($result->isOk());
        $this->assertTrue($result->unwrap()->isNone());
    }

    #[Test]
    public function isScaleValidメソッドのテスト(): void
    {
        // スケール内の値の場合
        $result1 = TestDecimalValue::isScaleValid(new Number('123.45'));
        $this->assertTrue($result1->isOk(), 'スケール2以内の値は有効');

        // スケールを超えた値の場合
        $result2 = TestDecimalValue::isScaleValid(new Number('123.456'));
        $this->assertFalse($result2->isOk(), 'スケール2を超える値は無効');

        // スケールがちょうど境界値の場合
        $result3 = TestDecimalValue::isScaleValid(new Number('123.45'));
        $this->assertTrue($result3->isOk(), 'スケール2の値は有効');
    }
}
