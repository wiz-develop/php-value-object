<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number\Integer;

use DivisionByZeroError;
use Error;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use ReflectionClass;
use Throwable;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\Examples\Number\Integer\TestIntegerValue;
use WizDevelop\PhpValueObject\Number\Integer\IntegerValueBase;
use WizDevelop\PhpValueObject\Number\IntegerValue;
use WizDevelop\PhpValueObject\Tests\TestCase;

/**
 * IntegerValue抽象クラスのテスト
 */
#[TestDox('IntegerValue抽象クラスのテスト')]
#[Group('IntegerValue')]
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
        $this->assertEquals(100, $integer->value);
    }

    #[Test]
    public function 最小値でインスタンスが作成できる(): void
    {
        $integer = TestIntegerValue::from(-1000);
        $this->assertEquals(-1000, $integer->value);
    }

    #[Test]
    public function 最大値でインスタンスが作成できる(): void
    {
        $integer = TestIntegerValue::from(1000);
        $this->assertEquals(1000, $integer->value);
    }

    #[Test]
    public function value関数で内部値を取得できる(): void
    {
        $integerValue = TestIntegerValue::from(123);
        $this->assertEquals(123, $integerValue->value);
    }

    #[Test]
    public function isZero関数でゼロかどうかを判定できる(): void
    {
        $zeroValue = TestIntegerValue::from(0);
        $nonZeroValue = TestIntegerValue::from(123);

        $this->assertTrue($zeroValue->isZero());
        $this->assertFalse($nonZeroValue->isZero());
    }

    #[Test]
    public function isPositive関数で正の値かどうかを判定できる(): void
    {
        $positiveValue = TestIntegerValue::from(123);
        $negativeValue = TestIntegerValue::from(-123);
        $zeroValue = TestIntegerValue::from(0);

        $this->assertTrue($positiveValue->isPositive());
        $this->assertFalse($negativeValue->isPositive());
        $this->assertFalse($zeroValue->isPositive());
    }

    #[Test]
    public function isNegative関数で負の値かどうかを判定できる(): void
    {
        $positiveValue = TestIntegerValue::from(123);
        $negativeValue = TestIntegerValue::from(-123);
        $zeroValue = TestIntegerValue::from(0);

        $this->assertFalse($positiveValue->isNegative());
        $this->assertTrue($negativeValue->isNegative());
        $this->assertFalse($zeroValue->isNegative());
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
        $this->assertEquals($validValue, $result->unwrap()->value);
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
        $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());
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
        $this->assertInstanceOf(ValueObjectError::class, $result1->unwrapErr());

        // 最大値超過
        $result2 = TestIntegerValue::tryFrom(1001);
        $this->assertFalse($result2->isOk());
        $this->assertInstanceOf(ValueObjectError::class, $result2->unwrapErr());

        // エラーメッセージに範囲情報が含まれているか確認
        $errorMessage = $result2->unwrapErr()->getMessage();
        $this->assertStringContainsString('-1000', $errorMessage); // 最小値
        $this->assertStringContainsString('1000', $errorMessage);  // 最大値
        $this->assertStringContainsString('1001', $errorMessage);  // 入力値
    }

    #[Test]
    public function tryFrom関数で有効な値を検証してインスタンス化できる(): void
    {
        // 有効な値
        $result1 = TestIntegerValue::tryFrom(123);
        $this->assertTrue($result1->isOk());
        $this->assertEquals(123, $result1->unwrap()->value);

        // 無効な値（範囲外）
        $result2 = TestIntegerValue::tryFrom(1001);
        $this->assertFalse($result2->isOk());
        $this->assertInstanceOf(ValueObjectError::class, $result2->unwrapErr());
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
        $this->assertEquals(123, $option2->unwrap()->value);
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
        $this->assertEquals(123, $result2->unwrap()->unwrap()->value);

        // 無効な非Null値の場合
        $result3 = TestIntegerValue::tryFromNullable(1001); // 範囲外
        $this->assertFalse($result3->isOk());
        $this->assertInstanceOf(ValueObjectError::class, $result3->unwrapErr());
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
        $this->assertEquals(300, $result->unwrap()->value);
    }

    #[Test]
    public function 算術演算子のテスト_減算(): void
    {
        $value1 = TestIntegerValue::from(200);
        $value2 = TestIntegerValue::from(100);

        $result = $value1->trySub($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals(100, $result->unwrap()->value);
    }

    #[Test]
    public function 算術演算子のテスト_乗算(): void
    {
        $value = TestIntegerValue::from(100);
        $multiplier = TestIntegerValue::from(2);

        $result = $value->tryMul($multiplier);
        $this->assertTrue($result->isOk());
        $this->assertEquals(200, $result->unwrap()->value);
    }

    #[Test]
    public function 算術演算子のテスト_除算(): void
    {
        $value = TestIntegerValue::from(100);
        $divisor = TestIntegerValue::from(2);

        $result = $value->tryDiv($divisor);
        $this->assertTrue($result->isOk());
        $this->assertEquals(50, $result->unwrap()->value);
    }

    #[Test]
    public function 算術演算子のテスト_除算_整数除算(): void
    {
        $value = TestIntegerValue::from(100);
        $divisor = TestIntegerValue::from(3);

        $result = $value->tryDiv($divisor);
        $this->assertTrue($result->isOk());
        $this->assertEquals(33, $result->unwrap()->value); // intdivによる整数除算
    }

    #[Test]
    public function 算術演算子のテスト_除算_ゼロ除算(): void
    {
        $value = TestIntegerValue::from(100);
        $divisor = TestIntegerValue::from(0);

        // tryDiv（例外を投げない）
        $result = $value->tryDiv($divisor);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());

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

        /**
         * @var Result<IntegerValueBase,ValueObjectError>
         * @phpstan-ignore-next-line
         */
        $result = $integer1->{$tryMethodName}($integer2);
        $this->assertInstanceOf(Result::class, $result);

        if ($shouldSucceed) {
            $this->assertTrue($result->isOk(), "演算 {$value1} {$operation} {$value2} は成功するべき");
            $this->assertEquals($expected, $result->unwrap()->value);
        } else {
            $this->assertFalse($result->isOk(), "演算 {$value1} {$operation} {$value2} は失敗するべき");
            $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());
        }

        // 例外を投げる通常メソッドのテスト
        if ($shouldSucceed) {
            try {
                /**
                 * @var IntegerValueBase
                 * @phpstan-ignore-next-line
                 */
                $methodResult = $integer1->{$operation}($integer2);
                $this->assertInstanceOf(IntegerValueBase::class, $methodResult);
                $this->assertEquals($expected, $methodResult->value);
            } catch (Exception $e) {
                $this->fail("演算 {$value1} {$operation} {$value2} は例外を投げるべきでない: " . $e->getMessage());
            }
        } else {
            // 除算で0の場合は特別処理
            if ($operation === 'div' && $value2 === 0) {
                $this->expectException(DivisionByZeroError::class);
                // @phpstan-ignore-next-line
                $integer1->{$operation}($integer2);
            } else {
                try {
                    // @phpstan-ignore-next-line
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

    // ------------------------------------------
    // 追加テスト: 複合的な演算と連鎖
    // ------------------------------------------

    #[Test]
    public function 複合的な算術演算のテスト(): void
    {
        $value1 = TestIntegerValue::from(10);
        $value2 = TestIntegerValue::from(20);
        $value3 = TestIntegerValue::from(2);

        // (10 + 20) * 2 = 60
        $result = $value1->add($value2)->mul($value3);
        $this->assertEquals(60, $result->value);

        // 10 * (20 + 2) = 10 * 22 = 220
        $addResult = $value2->add($value3);
        $mulResult = $value1->mul($addResult);
        $this->assertEquals(220, $mulResult->value);
    }

    #[Test]
    public function 演算の連鎖のテスト(): void
    {
        $value = TestIntegerValue::from(10);

        // (((10 + 5) - 3) * 2) = ((15 - 3) * 2) = (12 * 2) = 24
        $result = $value
            ->add(TestIntegerValue::from(5))
            ->sub(TestIntegerValue::from(3))
            ->mul(TestIntegerValue::from(2));

        $this->assertEquals(24, $result->value);
    }

    #[Test]
    public function 文字列表現のテスト(): void
    {
        $value1 = TestIntegerValue::from(123);
        $this->assertEquals('123', (string)$value1);

        $value2 = TestIntegerValue::from(-456);
        $this->assertEquals('-456', (string)$value2);

        $value3 = TestIntegerValue::from(0);
        $this->assertEquals('0', (string)$value3);
    }

    #[Test]
    public function シリアライズとデシリアライズのテスト(): void
    {
        $original = TestIntegerValue::from(123);
        $serialized = serialize($original);

        /** @var TestIntegerValue */
        $unserialized = unserialize($serialized);

        $this->assertEquals($original->value, $unserialized->value);
        $this->assertTrue($original->equals($unserialized));
    }

    // ------------------------------------------
    // アクセス制御のテスト
    // ------------------------------------------

    #[Test]
    public function コンストラクタはprivateアクセス修飾子を持つことを確認(): void
    {
        $reflectionClass = new ReflectionClass(TestIntegerValue::class);
        $constructor = $reflectionClass->getConstructor();

        $this->assertNotNull($constructor, 'コンストラクタが見つかりませんでした');
        $this->assertTrue($constructor->isPrivate(), 'コンストラクタはprivateでなければならない');
    }

    #[Test]
    public function privateコンストラクタへのアクセスを試みるとエラーとなることを確認(): void
    {
        $hasThrown = false;

        try {
            // コンストラクタへの直接アクセスを試みる（通常これはPHPで許可されていない）
            // 以下は単にエラーが発生することを確認するだけ
            // @phpstan-ignore-next-line
            new TestIntegerValue(100);
        } catch (Error $e) {
            $hasThrown = true;
            $this->assertStringContainsString(
                'private',
                $e->getMessage(),
                'エラーメッセージにprivateという文字列が含まれるべき'
            );
        }

        $this->assertTrue($hasThrown, 'privateコンストラクタへのアクセス時にはエラーが発生するべき');
    }

    // ------------------------------------------
    // jsonSerializeのテスト
    // ------------------------------------------

    #[Test]
    public function jsonSerializeメソッドは整数値を返す(): void
    {
        $integer = TestIntegerValue::from(123);
        $this->assertSame(123, $integer->jsonSerialize());

        $json = json_encode($integer);
        $this->assertSame('123', $json);

        $negativeInteger = TestIntegerValue::from(-456);
        $this->assertSame(-456, $negativeInteger->jsonSerialize());

        $json = json_encode($negativeInteger);
        $this->assertSame('-456', $json);
    }

    // ------------------------------------------
    // PHP_INT_MIN/MAXに近い値のテスト
    // ------------------------------------------

    #[Test]
    public function PHP整数の限界値付近の値のテスト(): void
    {
        // TestIntegerValueのmin/maxはIntegerValueBase::MIN_VALUEとIntegerValueBase::MAX_VALUEを制約として使用
        // これらはPHP_INT_MINとPHP_INT_MAXに設定されているが、実際のテストクラスではもっと狭い範囲を使用

        // 代わりに、通常の制約内で大きな値/小さな値をテスト
        $largeValue = TestIntegerValue::from(1000);  // TestIntegerValueの最大値
        $this->assertEquals(1000, $largeValue->value);

        $smallValue = TestIntegerValue::from(-1000);  // TestIntegerValueの最小値
        $this->assertEquals(-1000, $smallValue->value);

        // 範囲外の値
        $result1 = TestIntegerValue::tryFrom(1001);  // TestIntegerValueの最大値+1
        $this->assertFalse($result1->isOk());

        $result2 = TestIntegerValue::tryFrom(-1001);  // TestIntegerValueの最小値-1
        $this->assertFalse($result2->isOk());
    }

    // ------------------------------------------
    // IntegerValueFactoryのtryFromメソッドのテスト
    // ------------------------------------------

    #[Test]
    public function IntegerValueFactoryのtryFromメソッドは未実装のため常にResult_okを返す(): void
    {
        // IntegerValueFactoryのtryFromメソッドは抽象メソッドなので
        // 実装クラスで実装される必要がある
        $value = 100;
        $result = TestIntegerValue::tryFrom($value);

        $this->assertTrue($result->isOk());
        $this->assertEquals($value, $result->unwrap()->value);
    }

    #[Test]
    public function IntegerValueFactoryのtryFromメソッドは範囲外の値に対してエラーを返す(): void
    {
        // 範囲外の値をテスト
        $result = TestIntegerValue::tryFrom(1001);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());
    }

    /**
     * @return array<string, array{int, bool}>
     */
    public static function IntegerValueFactoryのtryFrom用のテストデータを提供(): array
    {
        return [
            '有効な正の値' => [100, true],
            '有効な負の値' => [-100, true],
            '有効なゼロ値' => [0, true],
            '有効な最小値' => [-1000, true],
            '有効な最大値' => [1000, true],
            '無効な最小値未満' => [-1001, false],
            '無効な最大値超過' => [1001, false],
        ];
    }

    #[Test]
    #[DataProvider('IntegerValueFactoryのtryFrom用のテストデータを提供')]
    public function IntegerValueFactoryのtryFromメソッドのデータ駆動テスト(int $value, bool $shouldSucceed): void
    {
        $result = TestIntegerValue::tryFrom($value);

        if ($shouldSucceed) {
            $this->assertTrue($result->isOk(), "値 {$value} は有効であるべき");
            $this->assertEquals($value, $result->unwrap()->value);
        } else {
            $this->assertFalse($result->isOk(), "値 {$value} は無効であるべき");
            $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());
        }
    }

    #[Test]
    public function IntegerValueFactoryのtryFromメソッドはisValidRangeメソッドを呼び出す(): void
    {
        // 有効な値の場合
        $validResult = TestIntegerValue::tryFrom(100);
        $this->assertTrue($validResult->isOk());

        // 無効な値の場合（範囲外）
        $invalidResult = TestIntegerValue::tryFrom(1001);
        $this->assertFalse($invalidResult->isOk());

        $errorMessage = $invalidResult->unwrapErr()->getMessage();
        $this->assertStringContainsString('1000', $errorMessage);
    }

    // ------------------------------------------
    // IntegerValueBaseのisValidRangeメソッドのテスト
    // ------------------------------------------

    #[Test]
    public function isValidRangeメソッドは有効な範囲に対してResult_okを返す(): void
    {
        // 有効な範囲（-1000から1000）
        $validValue = 100;
        $result = TestIntegerValue::tryFrom($validValue);
        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function isValidRangeメソッドは無効な範囲に対してResult_errを返す(): void
    {
        // 無効な範囲（1000を超える）
        $invalidValue = 1001;
        $result = TestIntegerValue::tryFrom($invalidValue);
        $this->assertFalse($result->isOk());

        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('整数', $errorMessage);
        $this->assertStringContainsString('-1000', $errorMessage);  // 最小値
        $this->assertStringContainsString('1000', $errorMessage);   // 最大値
        $this->assertStringContainsString('1001', $errorMessage);   // 入力値
    }

    /**
     * @return array<string, array{int, bool}>
     */
    public static function isValidRange用のテストデータを提供(): array
    {
        return [
            '有効な範囲_最小値' => [-1000, true],
            '有効な範囲_最大値' => [1000, true],
            '有効な範囲_ゼロ' => [0, true],
            '有効な範囲_正の値' => [500, true],
            '有効な範囲_負の値' => [-500, true],
            '有効な範囲_1' => [1, true],
            '有効な範囲_-1' => [-1, true],
            '無効な範囲_最小値未満' => [-1001, false],
            '無効な範囲_最大値超過' => [1001, false],
            '無効な範囲_大きな正の値' => [2000, false],
            '無効な範囲_大きな負の値' => [-2000, false],
            '無効な範囲_PHP_INT_MAX' => [PHP_INT_MAX, false],
            '無効な範囲_PHP_INT_MIN' => [PHP_INT_MIN, false],
        ];
    }

    #[Test]
    #[DataProvider('isValidRange用のテストデータを提供')]
    public function isValidRangeメソッドのデータ駆動テスト(int $value, bool $shouldSucceed): void
    {
        $result = TestIntegerValue::tryFrom($value);

        if ($shouldSucceed) {
            $this->assertTrue($result->isOk(), "値 {$value} は有効な範囲であるべき");
        } else {
            $this->assertFalse($result->isOk(), "値 {$value} は無効な範囲であるべき");
            $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());

            $errorMessage = $result->unwrapErr()->getMessage();
            $this->assertStringContainsString('整数', $errorMessage);
        }
    }

    #[Test]
    public function isValidRangeメソッドは境界値を正しく処理する(): void
    {
        // 境界値のテスト
        $boundaryTests = [
            [-1000, true],   // 最小値
            [-999, true],    // 最小値+1
            [999, true],     // 最大値-1
            [1000, true],    // 最大値
            [-1001, false],  // 最小値-1
            [1001, false],   // 最大値+1
        ];

        foreach ($boundaryTests as [$value, $expected]) {
            $result = TestIntegerValue::tryFrom($value);
            if ($expected) {
                $this->assertTrue($result->isOk(), "境界値 {$value} は有効であるべき");
            } else {
                $this->assertFalse($result->isOk(), "境界値 {$value} は無効であるべき");
            }
        }
    }

    #[Test]
    public function isValidRangeメソッドはエラーメッセージに適切な情報を含む(): void
    {
        // 範囲外の値でエラーメッセージを確認
        $result = TestIntegerValue::tryFrom(1500);
        $this->assertFalse($result->isOk());

        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('整数', $errorMessage);
        $this->assertStringContainsString('-1000', $errorMessage);  // 最小値
        $this->assertStringContainsString('1000', $errorMessage);   // 最大値
        $this->assertStringContainsString('1500', $errorMessage);   // 入力値
    }

    #[Test]
    public function isValidRangeメソッドは負の値でも正しく動作する(): void
    {
        // 負の範囲外の値
        $result = TestIntegerValue::tryFrom(-1500);
        $this->assertFalse($result->isOk());

        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('整数', $errorMessage);
        $this->assertStringContainsString('-1000', $errorMessage);  // 最小値
        $this->assertStringContainsString('1000', $errorMessage);   // 最大値
        $this->assertStringContainsString('-1500', $errorMessage);  // 入力値
    }

    // ------------------------------------------
    // zero()メソッドのテスト
    // ------------------------------------------

    #[Test]
    public function zero関数でゼロの値オブジェクトを生成できる(): void
    {
        $zeroValue = TestIntegerValue::zero();

        $this->assertInstanceOf(TestIntegerValue::class, $zeroValue);
        $this->assertEquals(0, $zeroValue->value);
        $this->assertTrue($zeroValue->isZero());
        $this->assertFalse($zeroValue->isPositive());
        $this->assertFalse($zeroValue->isNegative());
    }

    #[Test]
    public function zero関数で生成したインスタンスは他のゼロ値と等価である(): void
    {
        $zeroValue1 = TestIntegerValue::zero();
        $zeroValue2 = TestIntegerValue::from(0);

        $this->assertTrue($zeroValue1->equals($zeroValue2));
        $this->assertTrue($zeroValue2->equals($zeroValue1));
    }

    #[Test]
    public function zero関数で生成したインスタンスは算術演算で期待通りに動作する(): void
    {
        $zeroValue = TestIntegerValue::zero();
        $someValue = TestIntegerValue::from(123);

        // ゼロとの加算
        $addResult = $someValue->add($zeroValue);
        $this->assertTrue($addResult->equals($someValue));

        // ゼロとの減算
        $subResult = $someValue->sub($zeroValue);
        $this->assertTrue($subResult->equals($someValue));

        // ゼロとの乗算
        $mulResult = $someValue->mul($zeroValue);
        $this->assertTrue($mulResult->equals($zeroValue));
    }

    /**
     * @return array<string, array{int, bool, bool, bool}>
     */
    public static function 整数正負判定用のテストデータを提供(): array
    {
        return [
            '正の整数' => [100, false, true, false],
            '負の整数' => [-100, false, false, true],
            'ゼロ' => [0, true, false, false],
            '正の小さい値' => [1, false, true, false],
            '負の小さい値' => [-1, false, false, true],
            '大きな正の値' => [999, false, true, false],
            '大きな負の値' => [-999, false, false, true],
            '最小値' => [-1000, false, false, true],
            '最大値' => [1000, false, true, false],
        ];
    }

    /**
     * @param int  $value      テスト対象の値
     * @param bool $expectZero isZeroの期待値
     * @param bool $expectPos  isPositiveの期待値
     * @param bool $expectNeg  isNegativeの期待値
     */
    #[Test]
    #[DataProvider('整数正負判定用のテストデータを提供')]
    public function 整数正負判定メソッドのデータ駆動テスト(
        int $value,
        bool $expectZero,
        bool $expectPos,
        bool $expectNeg
    ): void {
        $integerValue = TestIntegerValue::from($value);

        $this->assertSame($expectZero, $integerValue->isZero(), "値 {$value} のisZero()結果が期待値と異なる");
        $this->assertSame($expectPos, $integerValue->isPositive(), "値 {$value} のisPositive()結果が期待値と異なる");
        $this->assertSame($expectNeg, $integerValue->isNegative(), "値 {$value} のisNegative()結果が期待値と異なる");
    }
}
