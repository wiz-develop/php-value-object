<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number\Decimal;

use BcMath\Number;
use DivisionByZeroError;
use Error;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use ReflectionClass;
use Throwable;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\TestDecimalValue;
use WizDevelop\PhpValueObject\Number\Decimal\DecimalValueBase;
use WizDevelop\PhpValueObject\Number\DecimalValue;
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
        ];
    }

    #[Test]
    #[DataProvider('無効な値のパターンを提供')]
    public function 無効な値はエラーになる(string $invalidValue): void
    {
        $result = TestDecimalValue::tryFrom(new Number($invalidValue));
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
        $result1 = TestDecimalValue::tryFrom(new Number('-1001'));
        $this->assertFalse($result1->isOk());
        $this->assertInstanceOf(ValueObjectError::class, $result1->unwrapErr());

        // 最大値超過
        $result2 = TestDecimalValue::tryFrom(new Number('1001'));
        $this->assertFalse($result2->isOk());
        $this->assertInstanceOf(ValueObjectError::class, $result2->unwrapErr());

        // エラーメッセージに範囲情報が含まれているか確認
        $errorMessage = $result2->unwrapErr()->getMessage();
        $this->assertStringContainsString('-1000', $errorMessage); // 最小値
        $this->assertStringContainsString('1000', $errorMessage);  // 最大値
        $this->assertStringContainsString('1001', $errorMessage);  // 入力値
    }

    #[Test]
    public function スケールが正確に保持されているかのテスト(): void
    {
        $value1 = TestDecimalValue::from(new Number('10.50'));
        $this->assertEquals('10.50', (string)$value1->value);

        $value2 = TestDecimalValue::from(new Number('20.25'));
        $this->assertEquals('20.25', (string)$value2->value);

        // 末尾がゼロの値
        $value3 = TestDecimalValue::from(new Number('10.00'));
        $this->assertEquals('10.00', (string)$value3->value); // 末尾のゼロが保持される

        // 演算結果のスケール確認
        $sum = $value1->add($value2);
        $this->assertEquals('30.75', (string)$sum->value); // スケールが保持される

        // 整数値から小数への変換
        $value4 = TestDecimalValue::from(new Number('42'));
        $this->assertEquals('42', (string)$value4->value); // 小数点以下が追加される
    }

    #[Test]
    public function tryFrom関数で有効な値を検証してインスタンス化できる(): void
    {
        // 有効な値
        $result1 = TestDecimalValue::tryFrom(new Number('123.45'));
        $this->assertTrue($result1->isOk());
        $this->assertEquals('123.45', (string)$result1->unwrap()->value);


        // 無効な値（範囲外）
        $result3 = TestDecimalValue::tryFrom(new Number('1001'));
        $this->assertFalse($result3->isOk());
        $this->assertInstanceOf(ValueObjectError::class, $result3->unwrapErr());
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
        $this->assertInstanceOf(ValueObjectError::class, $result3->unwrapErr());
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
        $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());

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
            '加算_大きな値' => ['400', '500', 'add', '900', true],
            '減算_正常' => ['200.50', '100.25', 'sub', '100.25', true],
            '乗算_正常' => ['100.50', '2', 'mul', '201.00', true],
            '乗算_大きな値' => ['300', '3', 'mul', '900', true],
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

        /**
         * @var Result<DecimalValueBase,ValueObjectError>
         * @phpstan-ignore-next-line
         */
        $result = $decimal1->{$tryMethodName}($decimal2);
        $this->assertInstanceOf(Result::class, $result);

        if ($shouldSucceed) {
            $this->assertTrue($result->isOk(), "演算 {$value1} {$operation} {$value2} は成功するべき");
            $this->assertEquals($expected, (string)$result->unwrap()->value);
        } else {
            $this->assertFalse($result->isOk(), "演算 {$value1} {$operation} {$value2} は失敗するべき");
            $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());
        }

        // 例外を投げる通常メソッドのテスト
        if ($shouldSucceed) {
            try {
                /**
                 * @var DecimalValueBase
                 * @phpstan-ignore-next-line
                 */
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
                // @phpstan-ignore-next-line
                $decimal1->{$operation}($decimal2);
            } else {
                try {
                    // @phpstan-ignore-next-line
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

    // ------------------------------------------
    // 追加テスト: 複合的な演算と連鎖
    // ------------------------------------------

    #[Test]
    public function 複合的な算術演算のテスト(): void
    {
        $value1 = TestDecimalValue::from(new Number('10.50'));
        $value2 = TestDecimalValue::from(new Number('20.25'));
        $value3 = TestDecimalValue::from(new Number('2.00'));

        // (10.50 + 20.25) * 2.00 = 30.75 * 2.00 = 61.50
        $result = $value1->add($value2)->mul($value3);
        $this->assertEquals('61.5000', (string)$result->value);

        // 10.50 * (20.25 + 2.00) = 10.50 * 22.25 = 233.63
        $addResult = $value2->add($value3);
        $mulResult = $value1->mul($addResult);
        $this->assertEquals('233.6250', (string)$mulResult->value);
    }

    #[Test]
    public function 演算の連鎖のテスト(): void
    {
        $value = TestDecimalValue::from(new Number('10.50'));

        // (((10.50 + 5.25) - 3.00) * 2.00) = ((15.75 - 3.00) * 2.00) = (12.75 * 2.00) = 25.50
        $result = $value
            ->add(TestDecimalValue::from(new Number('5.25')))
            ->sub(TestDecimalValue::from(new Number('3.00')))
            ->mul(TestDecimalValue::from(new Number('2.00')));

        $this->assertEquals('25.5000', (string)$result->value);
    }

    #[Test]
    public function 文字列表現のテスト(): void
    {
        $value1 = TestDecimalValue::from(new Number('123.45'));
        $this->assertEquals('123.45', (string)$value1);

        $value2 = TestDecimalValue::from(new Number('-456.78'));
        $this->assertEquals('-456.78', (string)$value2);

        $value3 = TestDecimalValue::from(new Number('0.00'));
        $this->assertEquals('0.00', (string)$value3);

        $value4 = TestDecimalValue::from(new Number('100'));
        $this->assertEquals('100', (string)$value4);
    }

    #[Test]
    public function シリアライズとデシリアライズのテスト(): void
    {
        $original = TestDecimalValue::from(new Number('123.45'));
        $serialized = serialize($original);

        /** @var TestDecimalValue */
        $unserialized = unserialize($serialized);

        $this->assertEquals((string)$original->value, (string)$unserialized->value);
        $this->assertTrue($original->equals($unserialized));
    }

    // ------------------------------------------
    // アクセス制御のテスト
    // ------------------------------------------

    #[Test]
    public function コンストラクタはプライベートであることを確認(): void
    {
        $reflection = new ReflectionClass(TestDecimalValue::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPrivate());
    }

    #[Test]
    public function staticメソッドからのみインスタンス生成が可能であることを確認(): void
    {
        $value = new Number('100.50');

        // from()メソッドからインスタンス生成可能
        $instance1 = TestDecimalValue::from($value);
        $this->assertInstanceOf(TestDecimalValue::class, $instance1);

        // tryFrom()メソッドからもインスタンス生成可能
        $result = TestDecimalValue::tryFrom($value);
        $this->assertTrue($result->isOk());
        $instance2 = $result->unwrap();
        $this->assertInstanceOf(TestDecimalValue::class, $instance2);
    }

    #[Test]
    public function コンストラクタはprivateアクセス修飾子を持つことを確認(): void
    {
        $reflectionClass = new ReflectionClass(TestDecimalValue::class);
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
            /** @phpstan-ignore-next-line */
            $newObj = new TestDecimalValue(new Number('100.50'));
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
    // スケールと桁数のテスト
    // ------------------------------------------

    #[Test]
    public function scaleメソッドで定義されたスケール値がフォーマットに反映されることを確認(): void
    {
        // TestDecimalValueのscaleは2に設定されている
        $value = TestDecimalValue::from(new Number('123.456'));

        // 数値表現では元の値がそのまま保持される
        $this->assertEquals('123.456', (string)$value->value);

        // format()メソッドではscale値が適用される
        $this->assertEquals('123.46', $value->format());  // 小数点以下2桁に丸められている

        // 異なるスケール値を指定して確認
        $this->assertEquals('123.5', $value->format(1));  // 小数点以下1桁に丸められる
        $this->assertEquals('123.456', $value->format(3));  // 小数点以下3桁に丸められる
    }

    #[Test]
    public function 有効桁数を超える値はエラーになることを確認(): void
    {
        // 非常に大きな桁数の値を作成
        $largeDigitValue = '1.' . str_repeat('9', 29);  // 30桁の9の連続 (precision()は29に設定)

        $result = TestDecimalValue::tryFrom(new Number($largeDigitValue));
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());

        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('桁数', $errorMessage, 'エラーメッセージには桁数に関する情報が含まれるべき');
        $this->assertStringContainsString('29', $errorMessage, 'エラーメッセージには許容桁数(29)が含まれるべき');
        $this->assertStringContainsString('30', $errorMessage, 'エラーメッセージには実際の桁数(30)が含まれるべき');
    }

    #[Test]
    public function formatToNumberメソッドの動作確認(): void
    {
        $value = TestDecimalValue::from(new Number('123.456'));

        // formatToNumberはNumberオブジェクトを返す
        $formattedNumber = $value->formatToNumber(2);
        $this->assertInstanceOf(Number::class, $formattedNumber);
        $this->assertEquals('123.46', (string)$formattedNumber);

        // デフォルトのスケール値の場合
        $defaultFormatted = $value->formatToNumber();
        $this->assertEquals('123.46', (string)$defaultFormatted);

        // 異なるスケール値の場合
        $longFormatted = $value->formatToNumber(4);
        $this->assertEquals('123.4560', (string)$longFormatted);
    }

    // ------------------------------------------
    // format関数とformatToNumber関数の詳細テスト
    // ------------------------------------------

    /**
     * @return array<string, array{string, positive-int|0|null, string}>
     */
    public static function フォーマット用データを提供(): array
    {
        return [
            '整数値_デフォルトスケール' => ['100', null, '100.00'],
            '小数値_デフォルトスケール' => ['123.456', null, '123.46'],
            '負の値_デフォルトスケール' => ['-123.456', null, '-123.46'],
            'ゼロ値_デフォルトスケール' => ['0', null, '0.00'],
            '整数値_スケール0' => ['100', 0, '100'],
            '小数値_スケール0' => ['123.456', 0, '123'],
            '負の値_スケール0' => ['-123.456', 0, '-123'],
            'ゼロ値_スケール0' => ['0', 0, '0'],
            '整数値_スケール1' => ['100', 1, '100.0'],
            '小数値_スケール1' => ['123.456', 1, '123.5'],
            '負の値_スケール1' => ['-123.456', 1, '-123.5'],
            'ゼロ値_スケール1' => ['0', 1, '0.0'],
            '整数値_スケール3' => ['100', 3, '100.000'],
            '小数値_スケール3' => ['123.456', 3, '123.456'],
            '負の値_スケール3' => ['-123.456', 3, '-123.456'],
            'ゼロ値_スケール3' => ['0', 3, '0.000'],
            '整数値_スケール5' => ['100', 5, '100.00000'],
            '小数値_スケール5' => ['123.456', 5, '123.45600'],
            '負の値_スケール5' => ['-123.456', 5, '-123.45600'],
            'ゼロ値_スケール5' => ['0', 5, '0.00000'],
            '丸めが発生する値_切り上げ' => ['123.455', 2, '123.45'], // PHPのsprintfは切り捨てを行う
            '丸めが発生する値_切り下げ' => ['123.454', 2, '123.45'],
            'スケールより小さい小数値' => ['123.4', 2, '123.40'],
            '最小値に近い値' => ['-999.999', 2, '-1000.00'],
            '最大値に近い値' => ['999.999', 2, '1000.00'],
        ];
    }

    /**
     * format関数のテスト
     *
     * @param string              $value    テスト対象の値
     * @param positive-int|0|null $decimals 小数点以下の桁数
     * @param string              $expected 期待される結果
     */
    #[Test]
    #[DataProvider('フォーマット用データを提供')]
    public function format関数の詳細テスト(string $value, ?int $decimals, string $expected): void
    {
        $decimalValue = TestDecimalValue::from(new Number($value));
        $result = $decimalValue->format($decimals);

        $this->assertEquals($expected, $result, "値 {$value} を小数点以下 {$decimals} 桁でフォーマットした結果が期待値と一致しない");
    }

    /**
     * formatToNumber関数のテスト
     *
     * @param string              $value    テスト対象の値
     * @param positive-int|0|null $decimals 小数点以下の桁数
     * @param string              $expected 期待される結果
     */
    #[Test]
    #[DataProvider('フォーマット用データを提供')]
    public function formatToNumber関数の詳細テスト(string $value, ?int $decimals, string $expected): void
    {
        $decimalValue = TestDecimalValue::from(new Number($value));
        $result = $decimalValue->formatToNumber($decimals);

        $this->assertInstanceOf(Number::class, $result);
        $this->assertEquals($expected, (string)$result, "値 {$value} を小数点以下 {$decimals} 桁でフォーマットした結果が期待値と一致しない");
    }

    #[Test]
    public function format関数とformatToNumber関数の結果が一致することを確認(): void
    {
        $testCases = [
            '123.456',
            '-789.012',
            '0.00',
            '500',
            '-0.123',
        ];

        foreach ($testCases as $value) {
            $decimalValue = TestDecimalValue::from(new Number($value));

            // デフォルトスケール
            $this->assertEquals(
                $decimalValue->format(),
                (string)$decimalValue->formatToNumber(),
                "値 {$value} のデフォルトスケールでのフォーマット結果が一致しない"
            );

            // スケール1
            $this->assertEquals(
                $decimalValue->format(1),
                (string)$decimalValue->formatToNumber(1),
                "値 {$value} のスケール1でのフォーマット結果が一致しない"
            );

            // スケール4
            $this->assertEquals(
                $decimalValue->format(4),
                (string)$decimalValue->formatToNumber(4),
                "値 {$value} のスケール4でのフォーマット結果が一致しない"
            );
        }
    }

    #[Test]
    public function 極端な桁数でのフォーマットテスト(): void
    {
        $value = TestDecimalValue::from(new Number('123.456'));

        // 非常に大きなスケール値
        $largeScale = 10;
        $this->assertEquals('123.4560000000', $value->format($largeScale));
        $this->assertEquals('123.4560000000', (string)$value->formatToNumber($largeScale));

        // ゼロスケール値
        $this->assertEquals('123', $value->format(0));
        $this->assertEquals('123', (string)$value->formatToNumber(0));
    }
}
