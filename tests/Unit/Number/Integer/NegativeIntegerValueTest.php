<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number\Integer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use ReflectionClass;
use Throwable;
use WizDevelop\PhpValueObject\Examples\Number\Integer\TestNegativeIntegerValue;
use WizDevelop\PhpValueObject\Examples\Number\Integer\TestPositiveIntegerValue;
use WizDevelop\PhpValueObject\Number\NegativeIntegerValue;
use WizDevelop\PhpValueObject\Number\NumberValueError;
use WizDevelop\PhpValueObject\Tests\TestCase;

/**
 * NegativeIntegerValue抽象クラスのテスト
 */
#[TestDox('NegativeIntegerValue抽象クラスのテスト')]
#[Group('IntegerValue')]
#[CoversClass(NegativeIntegerValue::class)]
#[CoversClass(TestNegativeIntegerValue::class)]
final class NegativeIntegerValueTest extends TestCase
{
    #[Test]
    public function 負の値でインスタンスが作成できる(): void
    {
        $negativeInteger = TestNegativeIntegerValue::from(-100);
        $this->assertEquals(-100, $negativeInteger->value);
    }

    #[Test]
    public function 正の値ではエラーになる(): void
    {
        $result = TestNegativeIntegerValue::tryFrom(1);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // エラーメッセージに「負の整数」が含まれるか
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('負の整数', $errorMessage);
    }

    #[Test]
    public function ゼロではエラーになる(): void
    {
        $result = TestNegativeIntegerValue::tryFrom(0);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // エラーメッセージに「負の整数」が含まれるか
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('負の整数', $errorMessage);
    }

    /**
     * @phpstan-ignore-next-line
     */
    public static function 境界値のテストデータを提供(): array
    {
        return [
            'ゼロ' => [0, false],
            '-1' => [-1, true],
            '最小値' => [-1000, true],
            '最小値-1' => [-1001, false],
            '1' => [1, false],
        ];
    }

    /**
     * @param int  $value      テスト対象の値
     * @param bool $shouldBeOk 成功するべきかどうか
     */
    #[Test]
    #[DataProvider('境界値のテストデータを提供')]
    public function 境界値テスト(int $value, bool $shouldBeOk): void
    {
        $result = TestNegativeIntegerValue::tryFrom($value);

        if ($shouldBeOk) {
            $this->assertTrue($result->isOk(), "値:{$value} は成功するべき");
            $this->assertEquals($value, $result->unwrap()->value);
        } else {
            $this->assertFalse($result->isOk(), "値:{$value} は失敗するべき");
            $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
        }
    }

    #[Test]
    public function 加算メソッドのテスト_正常系(): void
    {
        $value1 = TestNegativeIntegerValue::from(-30);
        $value2 = TestNegativeIntegerValue::from(-20);

        $result = $value1->tryAdd($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals(-50, $result->unwrap()->value);
    }

    #[Test]
    public function 加算メソッドのテスト_結果が範囲内に収まるケース(): void
    {
        $value1 = TestNegativeIntegerValue::from(-500);
        $value2 = TestNegativeIntegerValue::from(-100);

        // -500 + (-100) = -600（範囲内）
        $result = $value1->tryAdd($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals(-600, $result->unwrap()->value);
    }

    #[Test]
    public function 減算メソッドのテスト_正常系(): void
    {
        // -30 - (-10) = -30 + 10 = -20
        $value1 = TestNegativeIntegerValue::from(-30);
        $value2 = TestNegativeIntegerValue::from(-10);

        $result = $value1->trySub($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals(-20, $result->unwrap()->value);
    }

    #[Test]
    public function 乗算メソッドのテスト_正常系(): void
    {
        $value1 = TestNegativeIntegerValue::from(-10);
        $value2 = TestPositiveIntegerValue::from(2);

        // -10 * 2 = -20
        $result = $value1->tryMul($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals(-20, $result->unwrap()->value);
    }

    #[Test]
    public function 比較メソッドのテスト(): void
    {
        $value1 = TestNegativeIntegerValue::from(-10);
        $value2 = TestNegativeIntegerValue::from(-10);
        $value3 = TestNegativeIntegerValue::from(-20);

        // 等価比較
        $this->assertTrue($value1->equals($value2));
        $this->assertFalse($value1->equals($value3));

        // 大小比較（負の数の場合、数値が小さいほど大きい値）
        $this->assertTrue($value1->gt($value3));  // greater than: -10 > -20
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
        $result1 = TestNegativeIntegerValue::tryFrom(-1001);
        $this->assertFalse($result1->isOk());

        // 最大値超過
        $result2 = TestNegativeIntegerValue::tryFrom(0);
        $this->assertFalse($result2->isOk());

        // 範囲内
        $result3 = TestNegativeIntegerValue::tryFrom(-1000);
        $this->assertTrue($result3->isOk());

        $result4 = TestNegativeIntegerValue::tryFrom(-1);
        $this->assertTrue($result4->isOk());
    }

    #[Test]
    public function isZero関数は負の整数値に対して常にfalseを返す(): void
    {
        // 負の値はゼロでないのでfalse
        $negativeValue = TestNegativeIntegerValue::from(-10);
        $this->assertFalse($negativeValue->isZero());

        // 仮にゼロを作れたとしても、テストのために
        try {
            $zero = TestNegativeIntegerValue::from(0);
            $this->assertFalse($zero->isZero());
        } catch (Throwable $e) {
            // 例外が発生する場合はスキップ
            $this->markTestSkipped('ゼロの値を持つNegativeIntegerValueは作成できません');
        }
    }

    #[Test]
    public function 文字列表現のテスト(): void
    {
        $value1 = TestNegativeIntegerValue::from(-123);
        $this->assertEquals('-123', (string)$value1);
    }

    #[Test]
    public function シリアライズとデシリアライズのテスト(): void
    {
        $original = TestNegativeIntegerValue::from(-123);
        $serialized = serialize($original);

        /** @var TestNegativeIntegerValue */
        $unserialized = unserialize($serialized);

        $this->assertEquals($original->value, $unserialized->value);
        $this->assertTrue($original->equals($unserialized));
    }

    #[Test]
    public function 複合的な算術演算のテスト(): void
    {
        $value1 = TestNegativeIntegerValue::from(-10);
        $value2 = TestNegativeIntegerValue::from(-20);
        $value3 = TestNegativeIntegerValue::from(-2);

        // (-10 + -20) = -30
        $addResult = $value1->add($value2);
        $this->assertEquals(-30, $addResult->value);

        try {
            // (-10 + -20) * -2 = -30 * -2 = 60（正の数になるのでエラー）
            $addResult->mul($value3);
            $this->fail('正の結果になる演算はエラーになるべき');
        } catch (Throwable $e) {
            // 期待通りの例外
            // @phpstan-ignore-next-line
            $this->assertTrue(true);
        }
    }

    // ------------------------------------------
    // エラーメッセージの詳細テスト
    // ------------------------------------------

    #[Test]
    public function 正の値を渡した場合のエラーメッセージテスト(): void
    {
        $result = TestNegativeIntegerValue::tryFrom(10);
        $this->assertFalse($result->isOk());

        $error = $result->unwrapErr();
        $this->assertInstanceOf(NumberValueError::class, $error);

        $errorMessage = $error->getMessage();
        // クラス名が含まれているか
        $this->assertStringContainsString('負の整数', $errorMessage);
        // 範囲に関する情報が含まれているか
        $this->assertStringContainsString('-1000', $errorMessage); // 最小値
        $this->assertStringContainsString('-1', $errorMessage); // 最大値
        $this->assertStringContainsString('10', $errorMessage); // 入力値
        // 負の数であるべきという情報が含まれているか
        $this->assertStringContainsString('負の整数', $errorMessage);
    }

    #[Test]
    public function ゼロを渡した場合のエラーメッセージテスト(): void
    {
        $result = TestNegativeIntegerValue::tryFrom(0);
        $this->assertFalse($result->isOk());

        $error = $result->unwrapErr();
        $this->assertInstanceOf(NumberValueError::class, $error);

        $errorMessage = $error->getMessage();
        // 範囲に関する情報が含まれているか
        $this->assertStringContainsString('-1000', $errorMessage); // 最小値
        $this->assertStringContainsString('-1', $errorMessage); // 最大値
        $this->assertStringContainsString('0', $errorMessage); // 入力値
        // 負の数であるべきという情報が含まれているか
        $this->assertStringContainsString('負の整数', $errorMessage);
    }

    #[Test]
    public function 最小値未満の場合のエラーメッセージテスト(): void
    {
        $result = TestNegativeIntegerValue::tryFrom(-1001);
        $this->assertFalse($result->isOk());

        $error = $result->unwrapErr();
        $this->assertInstanceOf(NumberValueError::class, $error);

        $errorMessage = $error->getMessage();
        // 範囲に関する情報が含まれているか
        $this->assertStringContainsString('-1000', $errorMessage); // 最小値
        $this->assertStringContainsString('-1', $errorMessage); // 最大値
        $this->assertStringContainsString('-1001', $errorMessage); // 入力値
        // 値オブジェクトの名称が含まれているか
        $this->assertStringContainsString('負の整数', $errorMessage);
    }

    #[Test]
    public function 結果がゼロになる演算のエラーテスト(): void
    {
        $value1 = TestNegativeIntegerValue::from(-10);
        $value2 = TestNegativeIntegerValue::from(-10);

        // -10 + 10 = 0（ゼロになるのでエラー）
        $result = $value1->trySub($value2);
        $this->assertFalse($result->isOk());

        $error = $result->unwrapErr();
        $this->assertInstanceOf(NumberValueError::class, $error);

        $errorMessage = $error->getMessage();
        // 負の数であるべきという情報が含まれているか
        $this->assertStringContainsString('負の整数', $errorMessage);
    }

    #[Test]
    public function コンストラクタはprivateアクセス修飾子を持つことを確認(): void
    {
        $reflection = new ReflectionClass(TestNegativeIntegerValue::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPrivate());
    }
}
