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
use PHPUnit\Framework\TestCase;
use Throwable;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\TestDecimalValue;
use WizDevelop\PhpValueObject\Number\Decimal\DecimalValue;
use WizDevelop\PhpValueObject\Number\Decimal\IDecimalValue;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * DecimalValue抽象クラスの共通機能テスト
 */
#[TestDox('DecimalValue抽象クラスの共通機能テスト')]
#[CoversClass(DecimalValue::class)]
#[CoversClass(TestDecimalValue::class)]
final class DecimalValueCommonTest extends TestCase
{
    #[Test]
    public function value関数で内部値を取得できる(): void
    {
        $decimalValue = TestDecimalValue::from(new Number('123.45'));
        $this->assertEquals('123.45', (string)$decimalValue->value());
    }

    #[Test]
    public function isZero関数でゼロかどうかを判定できる(): void
    {
        $zeroValue = TestDecimalValue::from(new Number('0'));
        $nonZeroValue = TestDecimalValue::from(new Number('123.45'));

        $this->assertTrue($zeroValue->isZero());
        $this->assertFalse($nonZeroValue->isZero());
    }

    #[Test]
    public function fromNullable関数でNullを扱える(): void
    {
        // Null値の場合
        $option1 = TestDecimalValue::fromNullable(null);
        $this->assertTrue($option1->isNone());

        // 非Null値の場合
        $option2 = TestDecimalValue::fromNullable(new Number('123.45'));
        $this->assertTrue($option2->isSome());
        $this->assertEquals('123.45', (string)$option2->unwrap()->value());
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
        $this->assertEquals('123.45', (string)$result2->unwrap()->unwrap()->value());

        // 無効な非Null値の場合
        $result3 = TestDecimalValue::tryFromNullable(new Number('1001')); // 範囲外
        $this->assertFalse($result3->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result3->unwrapErr());
    }

    #[Test]
    public function scale関数でスケールを取得できる(): void
    {
        $this->assertEquals(2, TestDecimalValue::scale());
    }

    #[Test]
    public function min関数とmax関数で最小値と最大値を取得できる(): void
    {
        $min = TestDecimalValue::min();
        $max = TestDecimalValue::max();

        $this->assertEquals('-1000', (string)$min);
        $this->assertEquals('1000', (string)$max);
    }

    #[Test]
    public function isScaleValid関数でスケールの妥当性をチェックできる(): void
    {
        // 有効なスケール
        $result1 = TestDecimalValue::isScaleValid(new Number('123.45'));
        $this->assertTrue($result1->isOk());

        // 無効なスケール
        $result2 = TestDecimalValue::isScaleValid(new Number('123.456'));
        $this->assertFalse($result2->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result2->unwrapErr());

        // エラーメッセージにスケール情報が含まれていることを確認
        $errorMessage = $result2->unwrapErr()->getMessage();
        $this->assertStringContainsString('2', $errorMessage); // 期待されるスケール
        $this->assertStringContainsString('3', $errorMessage); // 実際のスケール
    }

    #[Test]
    public function isRangeValid関数で範囲の妥当性をチェックできる(): void
    {
        // 有効な範囲
        $result1 = TestDecimalValue::isRangeValid(new Number('500'));
        $this->assertTrue($result1->isOk());

        // 範囲外（下限以下）
        $result2 = TestDecimalValue::isRangeValid(new Number('-1001'));
        $this->assertFalse($result2->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result2->unwrapErr());

        // 範囲外（上限以上）
        $result3 = TestDecimalValue::isRangeValid(new Number('1001'));
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
        $result1 = TestDecimalValue::tryFrom(new Number('123.45'));
        $this->assertTrue($result1->isOk());
        $this->assertEquals('123.45', (string)$result1->unwrap()->value());

        // 無効な値（スケールオーバー）
        $result2 = TestDecimalValue::tryFrom(new Number('123.456'));
        $this->assertFalse($result2->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result2->unwrapErr());

        // 無効な値（範囲外）
        $result3 = TestDecimalValue::tryFrom(new Number('1001'));
        $this->assertFalse($result3->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result3->unwrapErr());
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

        /** @var Result<IDecimalValue,NumberValueError> */
        $result = $decimal1->{$tryMethodName}($decimal2);
        $this->assertInstanceOf(Result::class, $result);

        if ($shouldSucceed) {
            $this->assertTrue($result->isOk(), "演算 {$value1} {$operation} {$value2} は成功するべき");
            $this->assertEquals($expected, (string)$result->unwrap()->value());
        } else {
            var_dump($result);
            $this->assertFalse($result->isOk(), "演算 {$value1} {$operation} {$value2} は失敗するべき");
            $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
        }

        // 例外を投げる通常メソッドのテスト
        if ($shouldSucceed) {
            try {
                /** @var IDecimalValue */
                $methodResult = $decimal1->{$operation}($decimal2);
                $this->assertInstanceOf(IDecimalValue::class, $methodResult);
                $this->assertEquals($expected, (string)$methodResult->value());
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
