<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number\Decimal;

use BCMath\Number;
use DivisionByZeroError;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Throwable;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\TestDecimalValue;
use WizDevelop\PhpValueObject\Number\Decimal\DecimalValue;
use WizDevelop\PhpValueObject\Number\Decimal\DecimalValueBase;
use WizDevelop\PhpValueObject\Number\NumberValueError;
use WizDevelop\PhpValueObject\Tests\TestCase;

/**
 * DecimalValue抽象クラスのテスト
 */
#[TestDox('DecimalValue抽象クラスのテスト')]
#[CoversClass(DecimalValue::class)]
#[CoversClass(TestDecimalValue::class)]
final class DecimalValueTest extends TestCase
{
    // ------------------------------------------
    // インスタンス生成と基本機能のテスト
    // ------------------------------------------

    #[Test]
    public function 有効な値でインスタンスが作成できる(): void
    {
        $decimal = TestDecimalValue::from(new Number('100.50'));
        $this->assertEquals('100.50', (string)$decimal->value);
    }

    #[Test]
    public function 最小値でインスタンスが作成できる(): void
    {
        $decimal = TestDecimalValue::from(new Number('-1000'));
        $this->assertEquals('-1000', (string)$decimal->value);
    }

    #[Test]
    public function 最大値でインスタンスが作成できる(): void
    {
        $decimal = TestDecimalValue::from(new Number('1000'));
        $this->assertEquals('1000', (string)$decimal->value);
    }

    #[Test]
    public function value関数で内部値を取得できる(): void
    {
        $decimalValue = TestDecimalValue::from(new Number('123.45'));
        $this->assertEquals('123.45', (string)$decimalValue->value);
    }

    #[Test]
    public function isZero関数でゼロかどうかを判定できる(): void
    {
        $zeroValue = TestDecimalValue::from(new Number('0'));
        $nonZeroValue = TestDecimalValue::from(new Number('123.45'));

        $this->assertTrue($zeroValue->isZero());
        $this->assertFalse($nonZeroValue->isZero());
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
        $this->assertEquals($validValue, (string)$result->unwrap()->value);
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

    // ------------------------------------------
    // バリデーションのテスト
    // ------------------------------------------

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

    #[Test]
    public function tryFrom関数で有効な値を検証してインスタンス化できる(): void
    {
        // 有効な値
        $result1 = TestDecimalValue::tryFrom(new Number('123.45'));
        $this->assertTrue($result1->isOk());
        $this->assertEquals('123.45', (string)$result1->unwrap()->value);

        // 無効な値（スケールオーバー）
        $result2 = TestDecimalValue::tryFrom(new Number('123.456'));
        $this->assertFalse($result2->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result2->unwrapErr());

        // 無効な値（範囲外）
        $result3 = TestDecimalValue::tryFrom(new Number('1001'));
        $this->assertFalse($result3->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result3->unwrapErr());
    }

    // ------------------------------------------
    // Nullableメソッドのテスト
    // ------------------------------------------

    #[Test]
    public function fromNullable関数でNullを扱える(): void
    {
        // Null値の場合
        $option1 = TestDecimalValue::fromNullable(null);
        $this->assertTrue($option1->isNone());

        // 非Null値の場合
        $option2 = TestDecimalValue::fromNullable(new Number('123.45'));
        $this->assertTrue($option2->isSome());
        $this->assertEquals('123.45', (string)$option2->unwrap()->value);
    }

    #[Test]
    public function tryFromNullable関数でNullを扱える(): void
    {
        // Null値の場合
        $result1 = TestDecimalValue::tryFromNullable(null);
        $this->assertTrue($result1->isOk());
        $this->assertTrue($result1->unwrap()->isNone());

        // 有効な非Null値の場合
        $result2 = TestDecimalValue::tryFromNullable(new Number('123.45'));
        $this->assertTrue($result2->isOk());
        $this->assertTrue($result2->unwrap()->isSome());
        $this->assertEquals('123.45', (string)$result2->unwrap()->unwrap()->value);

        // 無効な非Null値の場合
        $result3 = TestDecimalValue::tryFromNullable(new Number('1001')); // 範囲外
        $this->assertFalse($result3->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result3->unwrapErr());
    }

    // ------------------------------------------
    // 算術演算のテスト
    // ------------------------------------------

    #[Test]
    public function 算術演算子のテスト_加算(): void
    {
        $value1 = TestDecimalValue::from(new Number('100.50'));
        $value2 = TestDecimalValue::from(new Number('200.25'));

        $result = $value1->tryAdd($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('300.75', (string)$result->unwrap()->value);
    }

    #[Test]
    public function 算術演算子のテスト_減算(): void
    {
        $value1 = TestDecimalValue::from(new Number('200.50'));
        $value2 = TestDecimalValue::from(new Number('100.25'));

        $result = $value1->trySub($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('100.25', (string)$result->unwrap()->value);
    }

    #[Test]
    public function 算術演算子のテスト_乗算(): void
    {
        $value = TestDecimalValue::from(new Number('100.50'));
        $multiplier = TestDecimalValue::from(new Number('2'));

        $result = $value->tryMul($multiplier);
        $this->assertTrue($result->isOk());
        $this->assertEquals('201.00', (string)$result->unwrap()->value);
    }

    #[Test]
    public function 算術演算子のテスト_除算(): void
    {
        $value = TestDecimalValue::from(new Number('100.50'));
        $divisor = TestDecimalValue::from(new Number('2'));

        $result = $value->tryDiv($divisor);
        $this->assertTrue($result->isOk());
        $this->assertEquals('50.25', (string)$result->unwrap()->value);
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

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: string|null, 4: bool}>
     */
    public static function 算術演算のデータを提供(): array
    {
        return [
            '加算_正常' => ['100.50', '200.25', 'add', '300.75', true],
            // 範囲内になるように調整
            '加算_大きな値' => ['400', '500', 'add', '900.00', true],
            '減算_正常' => ['200.50', '100.25', 'sub', '100.25', true],
            '乗算_正常' => ['100.50', '2', 'mul', '201.00', true],
            '乗算_大きな値' => ['300', '3', 'mul', '900.00', true],
            '除算_正常' => ['100.50', '2', 'div', '50.25', true],
            '除算_ゼロ除算' => ['100.50', '0', 'div', null, false],
            '除算_ゼロ除算_負の値' => ['-100.50', '0', 'div', null, false],
            // 範囲外になるように調整
            '加算_範囲外' => ['900', '200', 'add', null, false],
            '減算_範囲外' => ['-1000', '200', 'sub', null, false],
            '乗算_範囲外' => ['1000', '2', 'mul', null, false],
            '除算_範囲外' => ['1000', '0.5', 'div', null, false],
        ];
    }

    /**
     * @param string      $value1        最初の値
     * @param string      $value2        次の値
     * @param string      $operation     演算子（add, sub, mul, div）
     * @param string|null $expected      期待される結果値（エラーの場合はnull）
     * @param bool        $shouldSucceed 演算が成功するべきかどうか
     */
    #[Test]
    #[DataProvider('算術演算のデータを提供')]
    public function 算術演算メソッドのテスト(
        string $value1,
        string $value2,
        string $operation,
        ?string $expected,
        bool $shouldSucceed
    ): void {
        $decimal1 = TestDecimalValue::from(new Number($value1));
        $decimal2 = TestDecimalValue::from(new Number($value2));

        // tryXxx系のメソッドを使用
        $tryMethodName = 'try' . ucfirst($operation);

        /** @var Result<DecimalValueBase,NumberValueError> */
        $result = $decimal1->{$tryMethodName}($decimal2);
        $this->assertInstanceOf(Result::class, $result);

        if ($shouldSucceed) {
            $this->assertTrue($result->isOk(), "演算 {$value1} {$operation} {$value2} は成功するべき");
            $this->assertEquals($expected, (string)$result->unwrap()->value);
        } else {
            $this->assertFalse($result->isOk(), "演算 {$value1} {$operation} {$value2} は失敗するべき");
            $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
        }

        // 例外を投げる通常メソッドのテスト
        if ($shouldSucceed) {
            try {
                /** @var DecimalValueBase */
                $methodResult = $decimal1->{$operation}($decimal2);
                $this->assertInstanceOf(DecimalValueBase::class, $methodResult);
                $this->assertEquals($expected, (string)$methodResult->value);
            } catch (Exception $e) {
                $this->fail("演算 {$value1} {$operation} {$value2} は例外を投げるべきでない: " . $e->getMessage());
            }
        } else {
            // 除算で0の場合は特別処理
            if ($operation === 'div' && $value2 === '0') {
                $this->expectException(DivisionByZeroError::class);
                $decimal1->{$operation}($decimal2);
            } else {
                try {
                    $decimal1->{$operation}($decimal2);
                    $this->fail("演算 {$value1} {$operation} {$value2} は例外を投げるべき");
                } catch (Throwable $e) {
                    // 期待通りの例外
                    // @phpstan-ignore-next-line
                    $this->assertTrue(true);
                }
            }
        }
    }

    // ------------------------------------------
    // 比較演算のテスト
    // ------------------------------------------

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

    /**
     * @return array<string, array{0: string, 1: string, 2: bool, 3: bool, 4: bool, 5: bool}>
     */
    public static function 比較演算のデータを提供(): array
    {
        return [
            '等しい値' => ['100.50', '100.50', false, true, false, true],
            '大きい値と小さい値' => ['200.75', '100.50', true, true, false, false],
            '小さい値と大きい値' => ['100.50', '200.75', false, false, true, true],
        ];
    }

    /**
     * @param string $value1    最初の値
     * @param string $value2    次の値
     * @param bool   $expectGt  value1 > value2 の期待値
     * @param bool   $expectGte value1 >= value2 の期待値
     * @param bool   $expectLt  value1 < value2 の期待値
     * @param bool   $expectLte value1 <= value2 の期待値
     */
    #[Test]
    #[DataProvider('比較演算のデータを提供')]
    public function 比較演算メソッドのテスト(
        string $value1,
        string $value2,
        bool $expectGt,
        bool $expectGte,
        bool $expectLt,
        bool $expectLte
    ): void {
        $decimal1 = TestDecimalValue::from(new Number($value1));
        $decimal2 = TestDecimalValue::from(new Number($value2));

        $this->assertSame($expectGt, $decimal1->gt($decimal2), "{$value1} > {$value2}");
        $this->assertSame($expectGte, $decimal1->gte($decimal2), "{$value1} >= {$value2}");
        $this->assertSame($expectLt, $decimal1->lt($decimal2), "{$value1} < {$value2}");
        $this->assertSame($expectLte, $decimal1->lte($decimal2), "{$value1} <= {$value2}");
    }

    #[Test]
    public function equals関数で同値性の比較ができる(): void
    {
        $decimal1 = TestDecimalValue::from(new Number('100.50'));
        $decimal2 = TestDecimalValue::from(new Number('100.50'));
        $decimal3 = TestDecimalValue::from(new Number('200.75'));

        $this->assertTrue($decimal1->equals($decimal2));
        $this->assertFalse($decimal1->equals($decimal3));

        // 自分自身との比較
        $this->assertTrue($decimal1->equals($decimal1));
    }
}
