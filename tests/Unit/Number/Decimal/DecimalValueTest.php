<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number\Decimal;

use BCMath\Number;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\DecimalPrice;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\ZeroAllowedPositiveDecimal;
use WizDevelop\PhpValueObject\Number\NumberValueError;

#[TestDox('DecimalValueクラスのテスト')]
#[CoversClass(DecimalPrice::class)]
#[CoversClass(ZeroAllowedPositiveDecimal::class)]
final class DecimalValueTest extends TestCase
{
    #[Test]
    public function 有効な金額でインスタンスが作成できる(): void
    {
        $price = DecimalPrice::from(new Number('100.50'));

        $this->assertEquals('100.50', (string)$price->value());
    }

    #[Test]
    public function 最小値の金額でインスタンスが作成できる(): void
    {
        $price = DecimalPrice::from(new Number('0'));

        $this->assertEquals('0', (string)$price->value());
    }

    #[Test]
    public function 最大値の金額でインスタンスが作成できる(): void
    {
        $price = DecimalPrice::from(new Number('1000000'));

        $this->assertEquals('1000000', (string)$price->value());
    }

    #[Test]
    public function 負の値の場合はエラーになる(): void
    {
        $result = DecimalPrice::tryFrom(new Number('-10'));

        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // エラーメッセージに「金額」（ValueObjectMetaで指定した表示名）が含まれていることを確認
        $this->assertStringContainsString('金額', $result->unwrapErr()->getMessage());
    }

    #[Test]
    public function 最大値を超える値はエラーになる(): void
    {
        $result = DecimalPrice::tryFrom(new Number('1000000.01'));

        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // エラーメッセージにメタ情報の表示名と範囲情報が含まれていることを確認
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('金額', $errorMessage);
        $this->assertStringContainsString('1000000', $errorMessage);
    }

    #[Test]
    public function 小数点以下の桁数が多すぎる場合はエラーになる(): void
    {
        $result = DecimalPrice::tryFrom(new Number('100.123'));

        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // エラーメッセージにスケール情報が含まれていることを確認
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('金額', $errorMessage);
        $this->assertStringContainsString('2', $errorMessage); // スケール値
    }

    /**
     * @return array<string, array{string}>
     */
    public static function 有効な金額のパターンを提供(): array
    {
        return [
            '整数' => ['100'],
            '小数点以下1桁' => ['100.5'],
            '小数点以下2桁' => ['100.50'],
            'ゼロ' => ['0'],
            '小さな値' => ['0.01'],
            '最大値' => ['1000000'],
        ];
    }

    #[Test]
    #[DataProvider('有効な金額のパターンを提供')]
    public function 有効な値はインスタンスが作成できる(string $validValue): void
    {
        $result = DecimalPrice::tryFrom(new Number($validValue));

        $this->assertTrue($result->isOk());
        $this->assertEquals($validValue, (string)$result->unwrap()->value());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function 無効な金額のパターンを提供(): array
    {
        return [
            '負の値' => ['-1'],
            '小数点以下が多すぎる' => ['100.123'],
            '最大値超過' => ['1000000.01'],
        ];
    }

    #[Test]
    #[DataProvider('無効な金額のパターンを提供')]
    public function 無効な値はエラーになる(string $invalidValue): void
    {
        $result = DecimalPrice::tryFrom(new Number($invalidValue));

        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
    }

    #[Test]
    public function 算術演算子のテスト_加算(): void
    {
        $price1 = DecimalPrice::from(new Number('100.50'));
        $price2 = DecimalPrice::from(new Number('200.25'));

        $result = $price1->tryAdd($price2);

        $this->assertTrue($result->isOk());
        $this->assertEquals('300.75', (string)$result->unwrap()->value());
    }

    #[Test]
    public function 算術演算子のテスト_減算_正常系(): void
    {
        $price1 = DecimalPrice::from(new Number('200.50'));
        $price2 = DecimalPrice::from(new Number('100.25'));

        $result = $price1->trySub($price2);

        $this->assertTrue($result->isOk());
        $this->assertEquals('100.25', (string)$result->unwrap()->value());
    }

    #[Test]
    public function 算術演算子のテスト_減算_結果が負になる場合(): void
    {
        $price1 = DecimalPrice::from(new Number('100.50'));
        $price2 = DecimalPrice::from(new Number('200.75'));

        $result = $price1->trySub($price2);

        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
    }

    #[Test]
    public function 算術演算子のテスト_乗算(): void
    {
        $price = DecimalPrice::from(new Number('100.50'));
        $multiplier = DecimalPrice::from(new Number('2'));

        $result = $price->tryMul($multiplier);

        $this->assertTrue($result->isOk());
        $this->assertEquals('201.00', (string)$result->unwrap()->value());
    }

    #[Test]
    public function 算術演算子のテスト_除算(): void
    {
        $price = DecimalPrice::from(new Number('100.50'));
        $divisor = DecimalPrice::from(new Number('2'));

        $result = $price->tryDiv($divisor);

        $this->assertTrue($result->isOk());
        $this->assertEquals('50.25', (string)$result->unwrap()->value());
    }

    #[Test]
    public function 比較演算子のテスト_等価比較(): void
    {
        $price1 = DecimalPrice::from(new Number('100.50'));
        $price2 = DecimalPrice::from(new Number('100.50'));
        $price3 = DecimalPrice::from(new Number('200.75'));

        $this->assertTrue($price1->equals($price2));
        $this->assertFalse($price1->equals($price3));
    }

    #[Test]
    public function 比較演算子のテスト_大小比較(): void
    {
        $price1 = DecimalPrice::from(new Number('100.50'));
        $price2 = DecimalPrice::from(new Number('200.75'));

        $this->assertTrue($price1->lt($price2));
        $this->assertTrue($price1->lte($price2));
        $this->assertFalse($price1->gt($price2));
        $this->assertFalse($price1->gte($price2));

        $this->assertTrue($price2->gt($price1));
        $this->assertTrue($price2->gte($price1));
        $this->assertFalse($price2->lt($price1));
        $this->assertFalse($price2->lte($price1));
    }

    #[Test]
    public function メタ情報がエラーメッセージに反映される(): void
    {
        // 範囲エラーのケース
        $rangeErrorResult = DecimalPrice::tryFrom(new Number('1000001'));
        $rangeErrorMessage = $rangeErrorResult->unwrapErr()->getMessage();

        // スケールエラーのケース
        $scaleErrorResult = DecimalPrice::tryFrom(new Number('100.123'));
        $scaleErrorMessage = $scaleErrorResult->unwrapErr()->getMessage();

        // 負の値エラーのケース
        $negativeErrorResult = DecimalPrice::tryFrom(new Number('-10'));
        $negativeErrorMessage = $negativeErrorResult->unwrapErr()->getMessage();

        // どのエラーメッセージにも「金額」という表示名が含まれていることを確認
        $this->assertStringContainsString('金額', $rangeErrorMessage);
        $this->assertStringContainsString('金額', $scaleErrorMessage);
        $this->assertStringContainsString('金額', $negativeErrorMessage);
    }

    #[Test]
    public function NullableメソッドでNullを扱える(): void
    {
        $option = DecimalPrice::fromNullable(null);
        $this->assertTrue($option->isNone());

        $result = DecimalPrice::tryFromNullable(null);
        $this->assertTrue($result->isOk());
        $this->assertTrue($result->unwrap()->isNone());
    }

    #[Test]
    public function includeZeroメソッドの値によってゼロ値の扱いが変わる(): void
    {
        // DecimalPrice はincludeZero()がfalseなのでゼロを許容しない
        $this->assertFalse(DecimalPrice::includeZero());
        $result1 = DecimalPrice::tryFrom(new Number('0'));
        $this->assertTrue($result1->isOk(), 'DecimalPriceの最小値は0なので、0は有効な値として許容される');

        // ZeroAllowedPositiveDecimal はincludeZero()がtrueなのでゼロを許容する
        $this->assertTrue(ZeroAllowedPositiveDecimal::includeZero());
        $result2 = ZeroAllowedPositiveDecimal::tryFrom(new Number('0'));
        $this->assertTrue($result2->isOk(), 'ZeroAllowedPositiveDecimalはゼロを許容する');
    }

    #[Test]
    public function isPositiveメソッドはincludeZeroの値に応じてゼロ値を判定する(): void
    {
        // DecimalPrice (includeZero = false)
        $result1 = DecimalPrice::isPositive(new Number('0'));
        $this->assertFalse($result1->isOk(), 'DecimalPriceはincludeZero=falseなので、0は正の値として許容されない');

        $result2 = DecimalPrice::isPositive(new Number('0.01'));
        $this->assertTrue($result2->isOk(), '正の値は許容される');

        $result3 = DecimalPrice::isPositive(new Number('-0.01'));
        $this->assertFalse($result3->isOk(), '負の値は許容されない');

        // ZeroAllowedPositiveDecimal (includeZero = true)
        $result4 = ZeroAllowedPositiveDecimal::isPositive(new Number('0'));
        $this->assertTrue($result4->isOk(), 'ZeroAllowedPositiveDecimalはincludeZero=trueなので、0は正の値として許容される');

        $result5 = ZeroAllowedPositiveDecimal::isPositive(new Number('0.01'));
        $this->assertTrue($result5->isOk(), '正の値は許容される');

        $result6 = ZeroAllowedPositiveDecimal::isPositive(new Number('-0.01'));
        $this->assertFalse($result6->isOk(), '負の値は許容されない');
    }

    #[Test]
    public function isScaleValidメソッドのテスト(): void
    {
        // スケール内の値の場合
        $result1 = DecimalPrice::isScaleValid(new Number('123.45'));
        $this->assertTrue($result1->isOk(), 'スケール2以内の値は有効');

        // スケールを超えた値の場合
        $result2 = DecimalPrice::isScaleValid(new Number('123.456'));
        $this->assertFalse($result2->isOk(), 'スケール2を超える値は無効');

        // スケールがちょうど境界値の場合
        $result3 = DecimalPrice::isScaleValid(new Number('123.45'));
        $this->assertTrue($result3->isOk(), 'スケール2の値は有効');
    }
}
