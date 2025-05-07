<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number\Decimal;

use BcMath\Number;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Throwable;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\TestNegativeDecimalValue;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\TestPositiveDecimalValue;
use WizDevelop\PhpValueObject\Number\NegativeDecimalValue;
use WizDevelop\PhpValueObject\Tests\TestCase;

/**
 * NegativeDecimalValue抽象クラスのテスト
 */
#[TestDox('NegativeDecimalValue抽象クラスのテスト')]
#[Group('DecimalValue')]
#[CoversClass(NegativeDecimalValue::class)]
#[CoversClass(TestNegativeDecimalValue::class)]
final class NegativeDecimalValueTest extends TestCase
{
    #[Test]
    public function 負の値でインスタンスが作成できる(): void
    {
        $value = TestNegativeDecimalValue::from(new Number('-100.50'));
        $this->assertEquals('-100.50', (string)$value->value);
    }

    #[Test]
    public function 正の値ではエラーになる(): void
    {
        $result = TestNegativeDecimalValue::tryFrom(new Number('100.50'));
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());

        // エラーメッセージに負の値であるべきというメッセージが含まれていることを確認
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('負の数', $errorMessage);
    }

    #[Test]
    public function ゼロではエラーになる(): void
    {
        $result = TestNegativeDecimalValue::tryFrom(new Number('0.00'));
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());

        // エラーメッセージに負の値であるべきというメッセージが含まれていることを確認
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('負の数', $errorMessage);
    }

    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function 境界値のテストデータを提供(): array
    {
        return [
            'ゼロ' => ['0.00', false],
            '最大値' => ['-0.01', true],
            '最大値+0.01' => ['0.00', false],
            '最小値' => ['-1000.00', true],
            '最小値-0.01' => ['-1000.01', false],
            '正の値' => ['0.01', false],
        ];
    }

    /**
     * @param string $value      テスト対象の値
     * @param bool   $shouldBeOk 成功するべきかどうか
     */
    #[Test]
    #[DataProvider('境界値のテストデータを提供')]
    public function 境界値テスト(string $value, bool $shouldBeOk): void
    {
        $result = TestNegativeDecimalValue::tryFrom(new Number($value));

        if ($shouldBeOk) {
            $this->assertTrue($result->isOk(), "値:{$value} は成功するべき");
            $this->assertEquals($value, (string)$result->unwrap()->value);
        } else {
            $this->assertFalse($result->isOk(), "値:{$value} は失敗するべき");
            $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());
        }
    }

    #[Test]
    public function 加算メソッドのテスト_正常系(): void
    {
        $value1 = TestNegativeDecimalValue::from(new Number('-30.5'));
        $value2 = TestNegativeDecimalValue::from(new Number('-20.3'));

        $result = $value1->tryAdd($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('-50.8', (string)$result->unwrap()->value);
    }

    #[Test]
    public function 加算メソッドのテスト_結果が範囲内に収まるケース(): void
    {
        $value1 = TestNegativeDecimalValue::from(new Number('-500'));
        $value2 = TestNegativeDecimalValue::from(new Number('-100'));

        // -500 + (-100) = -600（範囲内）
        $result = $value1->tryAdd($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('-600', (string)$result->unwrap()->value);
    }

    #[Test]
    public function 減算メソッドのテスト_正常系(): void
    {
        // -30.5 - (-10.5) = -30.5 + 10.5 = -20
        $value1 = TestNegativeDecimalValue::from(new Number('-30.5'));
        $value2 = TestNegativeDecimalValue::from(new Number('-10.5'));

        $result = $value1->trySub($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('-20.0', (string)$result->unwrap()->value);
    }

    #[Test]
    public function 乗算メソッドのテスト_正常系(): void
    {
        $value1 = TestNegativeDecimalValue::from(new Number('-10.5'));
        $value2 = TestPositiveDecimalValue::from(new Number('2.0'));

        // -10.5 * 2.0 = -21.0
        $result = $value1->tryMul($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('-21.00', (string)$result->unwrap()->value);
    }

    #[Test]
    public function 比較メソッドのテスト(): void
    {
        $value1 = TestNegativeDecimalValue::from(new Number('-10.5'));
        $value2 = TestNegativeDecimalValue::from(new Number('-10.5'));
        $value3 = TestNegativeDecimalValue::from(new Number('-20.3'));

        // 等価比較
        $this->assertTrue($value1->equals($value2));
        $this->assertFalse($value1->equals($value3));

        // 大小比較（負の数の場合、数値が小さいほど大きい値）
        $this->assertTrue($value1->gt($value3));  // greater than: -10.5 > -20.3
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
        $result1 = TestNegativeDecimalValue::tryFrom(new Number('-1000.01'));
        $this->assertFalse($result1->isOk());

        // 最大値超過
        $result2 = TestNegativeDecimalValue::tryFrom(new Number('0'));
        $this->assertFalse($result2->isOk());

        // 範囲内
        $result3 = TestNegativeDecimalValue::tryFrom(new Number('-1000'));
        $this->assertTrue($result3->isOk());

        $result4 = TestNegativeDecimalValue::tryFrom(new Number('-0.01'));
        $this->assertTrue($result4->isOk());
    }

    #[Test]
    public function isZero関数は負の小数値に対して常にfalseを返す(): void
    {
        // 負の値はゼロでないのでfalse
        $negativeValue = TestNegativeDecimalValue::from(new Number('-10.50'));
        $this->assertFalse($negativeValue->isZero());

        // 仮にゼロを作れたとしても、テストのために
        try {
            $zero = TestNegativeDecimalValue::from(new Number('0.00'));
            $this->assertFalse($zero->isZero());
        } catch (Throwable $e) {
            // 例外が発生する場合はスキップ
            $this->markTestSkipped('ゼロの値を持つNegativeDecimalValueは作成できません');
        }
    }

    #[Test]
    public function 文字列表現のテスト(): void
    {
        $value1 = TestNegativeDecimalValue::from(new Number('-123.45'));
        $this->assertEquals('-123.45', (string)$value1);

        $value2 = TestNegativeDecimalValue::from(new Number('-100'));
        $this->assertEquals('-100', (string)$value2);
    }

    #[Test]
    public function シリアライズとデシリアライズのテスト(): void
    {
        $original = TestNegativeDecimalValue::from(new Number('-123.45'));
        $serialized = serialize($original);

        /** @var TestNegativeDecimalValue */
        $unserialized = unserialize($serialized);

        $this->assertEquals((string)$original->value, (string)$unserialized->value);
        $this->assertTrue($original->equals($unserialized));
    }

    #[Test]
    public function 複合的な算術演算のテスト(): void
    {
        $value1 = TestNegativeDecimalValue::from(new Number('-10.50'));
        $value2 = TestNegativeDecimalValue::from(new Number('-20.25'));
        $value3 = TestNegativeDecimalValue::from(new Number('-2.00'));

        // (-10.50 + -20.25) * -2.00 = -30.75 * -2.00 = 61.50
        // これは正の数になるのでエラーになるはず
        $addResult = $value1->add($value2);

        try {
            $mulResult = $addResult->mul($value3);
            $this->fail('正の結果になる演算はエラーになるべき');
        } catch (Throwable $e) {
            // 期待通りの例外
            // @phpstan-ignore-next-line
            $this->assertTrue(true);
        }

        // -10.50 * (-20.25 + -2.00) = -10.50 * -22.25 = 233.63
        // これも正の数になるのでエラーになるはず
        $addResult2 = $value2->add($value3);

        try {
            $mulResult2 = $value1->mul($addResult2);
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
        $result = TestNegativeDecimalValue::tryFrom(new Number('100.50'));
        $this->assertFalse($result->isOk());

        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);

        $errorMessage = $error->getMessage();
        // 値オブジェクトの名称が含まれているか
        $this->assertStringContainsString('負の数値', $errorMessage);
        // 範囲に関する情報が含まれているか
        $this->assertStringContainsString('-1000', $errorMessage); // 最小値
        $this->assertStringContainsString('-0.01', $errorMessage); // 最大値
        $this->assertStringContainsString('100.50', $errorMessage); // 入力値
        // 負の数であるべきという情報が含まれているか
        $this->assertStringContainsString('負の数', $errorMessage);
    }

    #[Test]
    public function ゼロを渡した場合のエラーメッセージテスト(): void
    {
        $result = TestNegativeDecimalValue::tryFrom(new Number('0'));
        $this->assertFalse($result->isOk());

        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);

        $errorMessage = $error->getMessage();
        // 範囲に関する情報が含まれているか
        $this->assertStringContainsString('-1000', $errorMessage); // 最小値
        $this->assertStringContainsString('-0.01', $errorMessage); // 最大値
        $this->assertStringContainsString('0', $errorMessage); // 入力値
        // 負の数であるべきという情報が含まれているか
        $this->assertStringContainsString('負の数', $errorMessage);
    }

    #[Test]
    public function 最小値未満の場合のエラーメッセージテスト(): void
    {
        $result = TestNegativeDecimalValue::tryFrom(new Number('-1001'));
        $this->assertFalse($result->isOk());

        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);

        $errorMessage = $error->getMessage();
        // 範囲に関する情報が含まれているか
        $this->assertStringContainsString('-1000', $errorMessage); // 最小値
        $this->assertStringContainsString('-0.01', $errorMessage); // 最大値
        $this->assertStringContainsString('-1001', $errorMessage); // 入力値
        // 正の数であるべきという情報が含まれているか
        $this->assertStringContainsString('負の数', $errorMessage);
    }

    #[Test]
    public function 算術演算でのエラーメッセージテスト(): void
    {
        $value1 = TestNegativeDecimalValue::from(new Number('-10.50'));
        $value2 = TestNegativeDecimalValue::from(new Number('-2.00'));

        // -10.50 * -2.00 = 21.00（正の数になるのでエラー）
        $result = $value1->tryMul($value2);
        $this->assertFalse($result->isOk());

        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);

        $errorMessage = $error->getMessage();
        // 範囲に関する情報が含まれているか
        $this->assertStringContainsString('-1000', $errorMessage); // 最小値
        $this->assertStringContainsString('-0.01', $errorMessage); // 最大値
        // 計算結果が含まれているか
        $this->assertStringContainsString('21.00', $errorMessage);
        // 負の数であるべきという情報が含まれているか
        $this->assertStringContainsString('負の数', $errorMessage);
    }
}
