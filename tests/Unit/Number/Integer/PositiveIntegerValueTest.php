<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number\Integer;

use AssertionError;
use Error;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use ReflectionClass;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\Examples\Number\Integer\TestPositiveIntegerValue;
use WizDevelop\PhpValueObject\Number\PositiveIntegerValue;
use WizDevelop\PhpValueObject\Tests\TestCase;

/**
 * PositiveIntegerValue抽象クラスのテスト
 */
#[TestDox('PositiveIntegerValue抽象クラスのテスト')]
#[Group('IntegerValue')]
#[CoversClass(PositiveIntegerValue::class)]
#[CoversClass(TestPositiveIntegerValue::class)]
final class PositiveIntegerValueTest extends TestCase
{
    #[Test]
    public function 正の値でインスタンスが作成できる(): void
    {
        $positiveInteger = TestPositiveIntegerValue::from(100);
        $this->assertEquals(100, $positiveInteger->value);
    }

    #[Test]
    public function 負の値ではエラーになる(): void
    {
        $result = TestPositiveIntegerValue::tryFrom(-1);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());

        // エラーメッセージに「正の整数」が含まれるか
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('正の整数', $errorMessage);
    }

    #[Test]
    public function ゼロではエラーになる(): void
    {
        $result = TestPositiveIntegerValue::tryFrom(0);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());

        // エラーメッセージに「正の整数」が含まれるか
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('正の整数', $errorMessage);
    }

    /**
     * @param int  $value      テスト対象の値
     * @param bool $shouldBeOk 成功するべきかどうか
     */
    #[Test]
    #[DataProvider('provide境界値テストCases')]
    public function 境界値テスト(int $value, bool $shouldBeOk): void
    {
        $result = TestPositiveIntegerValue::tryFrom($value);

        if ($shouldBeOk) {
            $this->assertTrue($result->isOk(), "値:{$value} は成功するべき");
            $this->assertEquals($value, $result->unwrap()->value);
        } else {
            $this->assertFalse($result->isOk(), "値:{$value} は失敗するべき");
            $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());
        }
    }

    /**
     * @phpstan-ignore-next-line
     */
    public static function provide境界値テストCases(): iterable
    {
        return [
            'ゼロ' => [0, false],
            '1' => [1, true],
            '最大値' => [1000, true],
            '最大値+1' => [1001, false],
            '-1' => [-1, false],
        ];
    }

    #[Test]
    public function 加算メソッドのテスト_正常系(): void
    {
        $value1 = TestPositiveIntegerValue::from(30);
        $value2 = TestPositiveIntegerValue::from(20);

        $result = $value1->tryAdd($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals(50, $result->unwrap()->value);
    }

    #[Test]
    public function 加算で結果が範囲外になる場合はエラーになる(): void
    {
        $value1 = TestPositiveIntegerValue::from(900);
        $value2 = TestPositiveIntegerValue::from(200);

        // 900 + 200 = 1100（最大値1000を超えるのでエラー）
        $result = $value1->tryAdd($value2);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());
    }

    #[Test]
    public function 減算メソッドのテスト_正常系(): void
    {
        // 30 - 10 = 20
        $value1 = TestPositiveIntegerValue::from(30);
        $value2 = TestPositiveIntegerValue::from(10);

        $result = $value1->trySub($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals(20, $result->unwrap()->value);
    }

    #[Test]
    public function 減算で結果が負になる場合はエラーになる(): void
    {
        $value1 = TestPositiveIntegerValue::from(10);
        $value2 = TestPositiveIntegerValue::from(20);

        // 10 - 20 = -10（負の値になるのでエラー）
        $result = $value1->trySub($value2);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());
    }

    #[Test]
    public function 乗算メソッドのテスト_正常系(): void
    {
        $value1 = TestPositiveIntegerValue::from(10);
        $value2 = TestPositiveIntegerValue::from(2);

        // 10 * 2 = 20
        $result = $value1->tryMul($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('20', (string)$result->unwrap()->value);
    }

    #[Test]
    public function 乗算で結果が範囲外になる場合はエラーになる(): void
    {
        $value1 = TestPositiveIntegerValue::from(500);
        $value2 = TestPositiveIntegerValue::from(3);

        // 500 * 3 = 1500（最大値1000を超えるのでエラー）
        $result = $value1->tryMul($value2);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());
    }

    #[Test]
    public function 比較メソッドのテスト(): void
    {
        $value1 = TestPositiveIntegerValue::from(10);
        $value2 = TestPositiveIntegerValue::from(10);
        $value3 = TestPositiveIntegerValue::from(20);

        // 等価比較
        $this->assertTrue($value1->equals($value2));
        $this->assertFalse($value1->equals($value3));

        // 大小比較
        $this->assertFalse($value1->gt($value3));  // greater than: 10 < 20
        $this->assertFalse($value1->gte($value3)); // greater than or equal
        $this->assertTrue($value1->gte($value2));  // greater than or equal (equal case)
        $this->assertTrue($value1->lt($value3));   // less than: 10 < 20
        $this->assertTrue($value1->lte($value3));  // less than or equal
        $this->assertTrue($value1->lte($value2));  // less than or equal (equal case)
    }

    #[Test]
    public function 範囲外の値はエラーになる(): void
    {
        // 最小値未満
        $result1 = TestPositiveIntegerValue::tryFrom(0);
        $this->assertFalse($result1->isOk());

        // 最大値超過
        $result2 = TestPositiveIntegerValue::tryFrom(1001);
        $this->assertFalse($result2->isOk());

        // 範囲内
        $result3 = TestPositiveIntegerValue::tryFrom(1);
        $this->assertTrue($result3->isOk());

        $result4 = TestPositiveIntegerValue::tryFrom(1000);
        $this->assertTrue($result4->isOk());
    }

    #[Test]
    public function isZero関数は正の整数値に対して常にfalseを返す(): void
    {
        // 正の値はゼロでないのでfalse
        $positiveValue = TestPositiveIntegerValue::from(10);
        $this->assertFalse($positiveValue->isZero());

        // 仮にゼロを作れたとしても、テストのために
        $this->expectException(AssertionError::class);
        $zero = TestPositiveIntegerValue::from(0); // AssertionErrorが発生する想定
    }

    #[Test]
    public function 文字列表現のテスト(): void
    {
        $value1 = TestPositiveIntegerValue::from(123);
        $this->assertEquals('123', (string)$value1);
    }

    #[Test]
    public function シリアライズとデシリアライズのテスト(): void
    {
        $original = TestPositiveIntegerValue::from(123);
        $serialized = serialize($original);

        /** @var TestPositiveIntegerValue */
        $unserialized = unserialize($serialized);

        $this->assertEquals($original->value, $unserialized->value);
        $this->assertTrue($original->equals($unserialized));
    }

    #[Test]
    public function 複合的な算術演算のテスト(): void
    {
        $value1 = TestPositiveIntegerValue::from(10);
        $value2 = TestPositiveIntegerValue::from(20);
        $value3 = TestPositiveIntegerValue::from(2);

        // (10 + 20) = 30
        $addResult = $value1->add($value2);
        $this->assertEquals(30, $addResult->value);

        // (10 + 20) * 2 = 30 * 2 = 60
        $mulResult = $addResult->mul($value3);
        $this->assertEquals(60, $mulResult->value);
    }

    // ------------------------------------------
    // エラーメッセージの詳細テスト
    // ------------------------------------------

    #[Test]
    public function 負の値を渡した場合のエラーメッセージテスト(): void
    {
        $result = TestPositiveIntegerValue::tryFrom(-10);
        $this->assertFalse($result->isOk());

        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);

        $errorMessage = $error->getMessage();
        // 値オブジェクトの名称が含まれているか
        $this->assertStringContainsString('正の整数', $errorMessage);
        // 範囲に関する情報が含まれているか
        $this->assertStringContainsString('1', $errorMessage); // 最小値
        $this->assertStringContainsString('1000', $errorMessage); // 最大値
        $this->assertStringContainsString('-10', $errorMessage); // 入力値
        // 正の数であるべきという情報が含まれているか
        $this->assertStringContainsString('正の整数', $errorMessage);
    }

    #[Test]
    public function ゼロを渡した場合のエラーメッセージテスト(): void
    {
        $result = TestPositiveIntegerValue::tryFrom(0);
        $this->assertFalse($result->isOk());

        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);

        $errorMessage = $error->getMessage();
        // 範囲に関する情報が含まれているか
        $this->assertStringContainsString('1', $errorMessage); // 最小値
        $this->assertStringContainsString('1000', $errorMessage); // 最大値
        $this->assertStringContainsString('0', $errorMessage); // 入力値
        // 正の数であるべきという情報が含まれているか
        $this->assertStringContainsString('正の整数', $errorMessage);
    }

    #[Test]
    public function 最大値超過の場合のエラーメッセージテスト(): void
    {
        $result = TestPositiveIntegerValue::tryFrom(1001);
        $this->assertFalse($result->isOk());

        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);

        $errorMessage = $error->getMessage();
        // 範囲に関する情報が含まれているか
        $this->assertStringContainsString('1', $errorMessage); // 最小値
        $this->assertStringContainsString('1000', $errorMessage); // 最大値
        $this->assertStringContainsString('1001', $errorMessage); // 入力値
        // 値オブジェクトの名称が含まれているか
        $this->assertStringContainsString('正の整数', $errorMessage);
    }

    #[Test]
    public function 除算で1未満になる場合のエラーテスト(): void
    {
        $value1 = TestPositiveIntegerValue::from(10);
        $value2 = TestPositiveIntegerValue::from(20);

        // 10 / 20 = 0（整数除算）
        // これは正の整数の範囲外（1未満）なのでエラーになるはず
        $result = $value1->tryDiv($value2);
        $this->assertFalse($result->isOk());

        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);

        $errorMessage = $error->getMessage();
        // 範囲に関する情報が含まれているか
        $this->assertStringContainsString('1', $errorMessage); // 最小値
        $this->assertStringContainsString('0', $errorMessage); // 演算結果
        // 正の数であるべきという情報が含まれているか
        $this->assertStringContainsString('正の整数', $errorMessage);
    }

    #[Test]
    public function コンストラクタはprivateアクセス修飾子を持つことを確認(): void
    {
        $reflection = new ReflectionClass(TestPositiveIntegerValue::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPrivate());
    }

    #[Test]
    public function privateコンストラクタへのアクセスを試みるとエラーとなることを確認(): void
    {
        $hasThrown = false;

        try {
            // コンストラクタへの直接アクセスを試みる（通常これはPHPで許可されていない）
            // 以下は単にエラーが発生することを確認するだけ
            /** @phpstan-ignore-next-line */
            $newObj = new TestPositiveIntegerValue(100);
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
}
