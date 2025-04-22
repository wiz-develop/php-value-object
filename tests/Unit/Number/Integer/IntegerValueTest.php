<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number\Integer;

use DivisionByZeroError;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Throwable;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Examples\Number\Integer\TestIntegerValue;
use WizDevelop\PhpValueObject\Number\Integer\IIntegerValue;
use WizDevelop\PhpValueObject\Number\Integer\IntegerValue;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * IntegerValue抽象クラスのテスト
 */
#[TestDox('IntegerValue抽象クラスのテスト')]
#[CoversClass(IntegerValue::class)]
#[CoversClass(TestIntegerValue::class)]
final class IntegerValueTest extends TestCase
{
    // ------------------------------------------
    // インスタンス生成と基本機能のテスト
    // ------------------------------------------

    #[Test]
    public function 有効な値でインスタンスが作成できる(): void
    {
        $integer = TestIntegerValue::from(100);
        $this->assertEquals(100, $integer->value());
    }

    #[Test]
    public function 最小値でインスタンスが作成できる(): void
    {
        $integer = TestIntegerValue::from(-1000);
        $this->assertEquals(-1000, $integer->value());
    }

    #[Test]
    public function 最大値でインスタンスが作成できる(): void
    {
        $integer = TestIntegerValue::from(1000);
        $this->assertEquals(1000, $integer->value());
    }

    #[Test]
    public function value関数で内部値を取得できる(): void
    {
        $integerValue = TestIntegerValue::from(123);
        $this->assertEquals(123, $integerValue->value());
    }

    #[Test]
    public function isZero関数でゼロかどうかを判定できる(): void
    {
        $zeroValue = TestIntegerValue::from(0);
        $nonZeroValue = TestIntegerValue::from(123);

        $this->assertTrue($zeroValue->isZero());
        $this->assertFalse($nonZeroValue->isZero());
    }

    /**
     * @return array<string, array{int}>
     */
    public static function 有効な値のパターンを提供(): array
    {
        return [
            '正数' => [100],
            '負数' => [-100],
            'ゼロ' => [0],
            '最小値' => [-1000],
            '最大値' => [1000],
        ];
    }

    #[Test]
    #[DataProvider('有効な値のパターンを提供')]
    public function 有効な値はインスタンスが作成できる(int $validValue): void
    {
        $result = TestIntegerValue::tryFrom($validValue);
        $this->assertTrue($result->isOk());
        $this->assertEquals($validValue, $result->unwrap()->value());
    }

    /**
     * @return array<string, array{int}>
     */
    public static function 無効な値のパターンを提供(): array
    {
        return [
            '最小値未満' => [-1001],
            '最大値超過' => [1001],
        ];
    }

    #[Test]
    #[DataProvider('無効な値のパターンを提供')]
    public function 無効な値はエラーになる(int $invalidValue): void
    {
        $result = TestIntegerValue::tryFrom($invalidValue);
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
        $result1 = TestIntegerValue::tryFrom(-1001);
        $this->assertFalse($result1->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result1->unwrapErr());

        // 最大値超過
        $result2 = TestIntegerValue::tryFrom(1001);
        $this->assertFalse($result2->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result2->unwrapErr());

        // エラーメッセージに範囲情報が含まれているか確認
        $errorMessage = $result2->unwrapErr()->getMessage();
        $this->assertStringContainsString('-1000', $errorMessage); // 最小値
        $this->assertStringContainsString('1000', $errorMessage);  // 最大値
        $this->assertStringContainsString('1001', $errorMessage);  // 入力値
    }

    #[Test]
    public function min関数とmax関数で最小値と最大値を取得できる(): void
    {
        $min = TestIntegerValue::min();
        $max = TestIntegerValue::max();

        $this->assertEquals(-1000, $min);
        $this->assertEquals(1000, $max);
    }

    #[Test]
    public function isRangeValid関数で範囲の妥当性をチェックできる(): void
    {
        // 有効な範囲
        $result1 = TestIntegerValue::isRangeValid(500);
        $this->assertTrue($result1->isOk());

        // 範囲外（下限以下）
        $result2 = TestIntegerValue::isRangeValid(-1001);
        $this->assertFalse($result2->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result2->unwrapErr());

        // 範囲外（上限以上）
        $result3 = TestIntegerValue::isRangeValid(1001);
        $this->assertFalse($result3->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result3->unwrapErr());

        // エラーメッセージに範囲情報が含まれていることを確認
        $errorMessage = $result3->unwrapErr()->getMessage();
        $this->assertStringContainsString('-1000', $errorMessage); // 最小値
        $this->assertStringContainsString('1000', $errorMessage);  // 最大値
    }

    #[Test]
    public function tryFrom関数で有効な値を検証してインスタンス化できる(): void
    {
        // 有効な値
        $result1 = TestIntegerValue::tryFrom(123);
        $this->assertTrue($result1->isOk());
        $this->assertEquals(123, $result1->unwrap()->value());

        // 無効な値（範囲外）
        $result2 = TestIntegerValue::tryFrom(1001);
        $this->assertFalse($result2->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result2->unwrapErr());
    }

    // ------------------------------------------
    // Nullableメソッドのテスト
    // ------------------------------------------

    #[Test]
    public function fromNullable関数でNullを扱える(): void
    {
        // Null値の場合
        $option1 = TestIntegerValue::fromNullable(null);
        $this->assertTrue($option1->isNone());

        // 非Null値の場合
        $option2 = TestIntegerValue::fromNullable(123);
        $this->assertTrue($option2->isSome());
        $this->assertEquals(123, $option2->unwrap()->value());
    }

    #[Test]
    public function tryFromNullable関数でNullを扱える(): void
    {
        // Null値の場合
        $result1 = TestIntegerValue::tryFromNullable(null);
        $this->assertTrue($result1->isOk());
        $this->assertTrue($result1->unwrap()->isNone());

        // 有効な非Null値の場合
        $result2 = TestIntegerValue::tryFromNullable(123);
        $this->assertTrue($result2->isOk());
        $this->assertTrue($result2->unwrap()->isSome());
        $this->assertEquals(123, $result2->unwrap()->unwrap()->value());

        // 無効な非Null値の場合
        $result3 = TestIntegerValue::tryFromNullable(1001); // 範囲外
        $this->assertFalse($result3->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result3->unwrapErr());
    }

    // ------------------------------------------
    // 算術演算のテスト
    // ------------------------------------------

    #[Test]
    public function 算術演算子のテスト_加算(): void
    {
        $value1 = TestIntegerValue::from(100);
        $value2 = TestIntegerValue::from(200);

        $result = $value1->tryAdd($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals(300, $result->unwrap()->value());
    }

    #[Test]
    public function 算術演算子のテスト_減算(): void
    {
        $value1 = TestIntegerValue::from(200);
        $value2 = TestIntegerValue::from(100);

        $result = $value1->trySub($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals(100, $result->unwrap()->value());
    }

    #[Test]
    public function 算術演算子のテスト_乗算(): void
    {
        $value = TestIntegerValue::from(100);
        $multiplier = TestIntegerValue::from(2);

        $result = $value->tryMul($multiplier);
        $this->assertTrue($result->isOk());
        $this->assertEquals(200, $result->unwrap()->value());
    }

    #[Test]
    public function 算術演算子のテスト_除算(): void
    {
        $value = TestIntegerValue::from(100);
        $divisor = TestIntegerValue::from(2);

        $result = $value->tryDiv($divisor);
        $this->assertTrue($result->isOk());
        $this->assertEquals(50, $result->unwrap()->value());
    }

    #[Test]
    public function 算術演算子のテスト_除算_整数除算(): void
    {
        $value = TestIntegerValue::from(100);
        $divisor = TestIntegerValue::from(3);

        $result = $value->tryDiv($divisor);
        $this->assertTrue($result->isOk());
        $this->assertEquals(33, $result->unwrap()->value()); // intdivによる整数除算
    }

    #[Test]
    public function 算術演算子のテスト_除算_ゼロ除算(): void
    {
        $value = TestIntegerValue::from(100);
        $divisor = TestIntegerValue::from(0);

        // tryDiv（例外を投げない）
        $result = $value->tryDiv($divisor);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // div（例外を投げる）
        $this->expectException(DivisionByZeroError::class);
        $value->div($divisor);
    }

    /**
     * @return array<string, array{0: int, 1: int, 2: string, 3: int|null, 4: bool}>
     */
    public static function 算術演算のデータを提供(): array
    {
        return [
            '加算_正常' => [100, 200, 'add', 300, true],
            // 範囲内になるように調整
            '加算_大きな値' => [400, 500, 'add', 900, true],
            '減算_正常' => [200, 100, 'sub', 100, true],
            '乗算_正常' => [100, 2, 'mul', 200, true],
            '乗算_大きな値' => [300, 3, 'mul', 900, true],
            '除算_正常' => [100, 2, 'div', 50, true],
            '除算_切り捨て' => [100, 3, 'div', 33, true], // intdivによる整数除算
            '除算_ゼロ除算' => [100, 0, 'div', null, false],
            '除算_ゼロ除算_負の値' => [-100, 0, 'div', null, false],
            // 範囲外になるように調整
            '加算_範囲外' => [900, 200, 'add', null, false],
            '減算_範囲外' => [-1000, 200, 'sub', null, false],
            '乗算_範囲外' => [500, 3, 'mul', null, false],
        ];
    }

    /**
     * @param int      $value1        最初の値
     * @param int      $value2        次の値
     * @param string   $operation     演算子（add, sub, mul, div）
     * @param int|null $expected      期待される結果値（エラーの場合はnull）
     * @param bool     $shouldSucceed 演算が成功するべきかどうか
     */
    #[Test]
    #[DataProvider('算術演算のデータを提供')]
    public function 算術演算メソッドのテスト(
        int $value1,
        int $value2,
        string $operation,
        ?int $expected,
        bool $shouldSucceed
    ): void {
        $integer1 = TestIntegerValue::from($value1);
        $integer2 = TestIntegerValue::from($value2);

        // tryXxx系のメソッドを使用
        $tryMethodName = 'try' . ucfirst($operation);

        /** @var Result<IIntegerValue,NumberValueError> */
        $result = $integer1->{$tryMethodName}($integer2);
        $this->assertInstanceOf(Result::class, $result);

        if ($shouldSucceed) {
            $this->assertTrue($result->isOk(), "演算 {$value1} {$operation} {$value2} は成功するべき");
            $this->assertEquals($expected, $result->unwrap()->value());
        } else {
            $this->assertFalse($result->isOk(), "演算 {$value1} {$operation} {$value2} は失敗するべき");
            $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
        }

        // 例外を投げる通常メソッドのテスト
        if ($shouldSucceed) {
            try {
                /** @var IIntegerValue */
                $methodResult = $integer1->{$operation}($integer2);
                $this->assertInstanceOf(IIntegerValue::class, $methodResult);
                $this->assertEquals($expected, $methodResult->value());
            } catch (Exception $e) {
                $this->fail("演算 {$value1} {$operation} {$value2} は例外を投げるべきでない: " . $e->getMessage());
            }
        } else {
            // 除算で0の場合は特別処理
            if ($operation === 'div' && $value2 === 0) {
                $this->expectException(DivisionByZeroError::class);
                $integer1->{$operation}($integer2);
            } else {
                try {
                    $integer1->{$operation}($integer2);
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
        $value1 = TestIntegerValue::from(100);
        $value2 = TestIntegerValue::from(100);
        $value3 = TestIntegerValue::from(200);

        $this->assertTrue($value1->equals($value2));
        $this->assertFalse($value1->equals($value3));
    }

    #[Test]
    public function 比較演算子のテスト_大小比較(): void
    {
        $value1 = TestIntegerValue::from(100);
        $value2 = TestIntegerValue::from(200);

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
     * @return array<string, array{0: int, 1: int, 2: bool, 3: bool, 4: bool, 5: bool}>
     */
    public static function 比較演算のデータを提供(): array
    {
        return [
            '等しい値' => [100, 100, false, true, false, true],
            '大きい値と小さい値' => [200, 100, true, true, false, false],
            '小さい値と大きい値' => [100, 200, false, false, true, true],
        ];
    }

    /**
     * @param int  $value1    最初の値
     * @param int  $value2    次の値
     * @param bool $expectGt  value1 > value2 の期待値
     * @param bool $expectGte value1 >= value2 の期待値
     * @param bool $expectLt  value1 < value2 の期待値
     * @param bool $expectLte value1 <= value2 の期待値
     */
    #[Test]
    #[DataProvider('比較演算のデータを提供')]
    public function 比較演算メソッドのテスト(
        int $value1,
        int $value2,
        bool $expectGt,
        bool $expectGte,
        bool $expectLt,
        bool $expectLte
    ): void {
        $integer1 = TestIntegerValue::from($value1);
        $integer2 = TestIntegerValue::from($value2);

        $this->assertSame($expectGt, $integer1->gt($integer2), "{$value1} > {$value2}");
        $this->assertSame($expectGte, $integer1->gte($integer2), "{$value1} >= {$value2}");
        $this->assertSame($expectLt, $integer1->lt($integer2), "{$value1} < {$value2}");
        $this->assertSame($expectLte, $integer1->lte($integer2), "{$value1} <= {$value2}");
    }

    #[Test]
    public function equals関数で同値性の比較ができる(): void
    {
        $integer1 = TestIntegerValue::from(100);
        $integer2 = TestIntegerValue::from(100);
        $integer3 = TestIntegerValue::from(200);

        $this->assertTrue($integer1->equals($integer2));
        $this->assertFalse($integer1->equals($integer3));

        // 自分自身との比較
        $this->assertTrue($integer1->equals($integer1));
    }
}
