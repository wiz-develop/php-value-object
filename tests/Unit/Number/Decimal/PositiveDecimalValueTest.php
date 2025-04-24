<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number\Decimal;

use BCMath\Number;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Throwable;
use WizDevelop\PhpValueObject\Examples\Number\Decimal\TestPositiveDecimalValue;
use WizDevelop\PhpValueObject\Number\NumberValueError;
use WizDevelop\PhpValueObject\Number\PositiveDecimalValue;
use WizDevelop\PhpValueObject\Tests\TestCase;

/**
 * PositiveDecimalValue抽象クラスのテスト
 */
#[TestDox('PositiveDecimalValue抽象クラスのテスト')]
#[Group('DecimalValue')]
#[CoversClass(PositiveDecimalValue::class)]
#[CoversClass(TestPositiveDecimalValue::class)]
final class PositiveDecimalValueTest extends TestCase
{
    #[Test]
    public function 正の値でインスタンスが作成できる(): void
    {
        $value = TestPositiveDecimalValue::from(new Number('100.50'));
        $this->assertEquals('100.50', (string)$value->value);
    }

    #[Test]
    public function 負の値ではエラーになる(): void
    {
        $result = TestPositiveDecimalValue::tryFrom(new Number('-100.50'));
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // エラーメッセージに正の値であるべきというメッセージが含まれていることを確認
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('正の数', $errorMessage);
    }

    #[Test]
    public function ゼロではエラーになる(): void
    {
        $result = TestPositiveDecimalValue::tryFrom(new Number('0.00'));
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // エラーメッセージに正の値であるべきというメッセージが含まれていることを確認
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('正の数', $errorMessage);
    }

    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function 境界値のテストデータを提供(): array
    {
        return [
            'ゼロ' => ['0.00', false],
            '最小値' => ['0.01', true],
            '最小値-0.01' => ['0.00', false],
            '最大値' => ['1000.00', true],
            '最大値+0.01' => ['1000.01', false],
            '負の値' => ['-0.01', false],
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
        $result = TestPositiveDecimalValue::tryFrom(new Number($value));

        if ($shouldBeOk) {
            $this->assertTrue($result->isOk(), "値:{$value} は成功するべき");
            $this->assertEquals($value, (string)$result->unwrap()->value);
        } else {
            $this->assertFalse($result->isOk(), "値:{$value} は失敗するべき");
            $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
        }
    }

    #[Test]
    public function 加算メソッドのテスト_正常系(): void
    {
        $value1 = TestPositiveDecimalValue::from(new Number('30.5'));
        $value2 = TestPositiveDecimalValue::from(new Number('20.3'));

        $result = $value1->tryAdd($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('50.8', (string)$result->unwrap()->value);
    }

    #[Test]
    public function 加算で結果が範囲外になる場合はエラーになる(): void
    {
        $value1 = TestPositiveDecimalValue::from(new Number('900'));
        $value2 = TestPositiveDecimalValue::from(new Number('200'));

        // 900 + 200 = 1100（最大値1000を超えるのでエラー）
        $result = $value1->tryAdd($value2);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
    }

    #[Test]
    public function 減算メソッドのテスト_正常系(): void
    {
        // 30.5 - 10.5 = 20.0
        $value1 = TestPositiveDecimalValue::from(new Number('30.5'));
        $value2 = TestPositiveDecimalValue::from(new Number('10.5'));

        $result = $value1->trySub($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('20.0', (string)$result->unwrap()->value);
    }

    #[Test]
    public function 減算で結果が負になる場合はエラーになる(): void
    {
        $value1 = TestPositiveDecimalValue::from(new Number('10.5'));
        $value2 = TestPositiveDecimalValue::from(new Number('20.5'));

        // 10.5 - 20.5 = -10.0（負の値になるのでエラー）
        $result = $value1->trySub($value2);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
    }

    #[Test]
    public function 乗算メソッドのテスト_正常系(): void
    {
        $value1 = TestPositiveDecimalValue::from(new Number('10.5'));
        $value2 = TestPositiveDecimalValue::from(new Number('2.0'));

        // 10.5 * 2.0 = 21.0
        $result = $value1->tryMul($value2);
        $this->assertTrue($result->isOk());
        $this->assertEquals('21.00', (string)$result->unwrap()->value);
    }

    #[Test]
    public function 乗算で結果が範囲外になる場合はエラーになる(): void
    {
        $value1 = TestPositiveDecimalValue::from(new Number('500'));
        $value2 = TestPositiveDecimalValue::from(new Number('3'));

        // 500 * 3 = 1500（最大値1000を超えるのでエラー）
        $result = $value1->tryMul($value2);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
    }

    #[Test]
    public function 比較メソッドのテスト(): void
    {
        $value1 = TestPositiveDecimalValue::from(new Number('10.5'));
        $value2 = TestPositiveDecimalValue::from(new Number('10.5'));
        $value3 = TestPositiveDecimalValue::from(new Number('20.3'));

        // 等価比較
        $this->assertTrue($value1->equals($value2));
        $this->assertFalse($value1->equals($value3));

        // 大小比較
        $this->assertFalse($value1->gt($value3));  // greater than: 10.5 < 20.3
        $this->assertFalse($value1->gte($value3)); // greater than or equal
        $this->assertTrue($value1->gte($value2));  // greater than or equal (equal case)
        $this->assertTrue($value1->lt($value3));   // less than: 10.5 < 20.3
        $this->assertTrue($value1->lte($value3));  // less than or equal
        $this->assertTrue($value1->lte($value2));  // less than or equal (equal case)
    }

    #[Test]
    public function 範囲外の値はエラーになる(): void
    {
        // 最小値未満
        $result1 = TestPositiveDecimalValue::tryFrom(new Number('0'));
        $this->assertFalse($result1->isOk());

        // 最大値超過
        $result2 = TestPositiveDecimalValue::tryFrom(new Number('1000.01'));
        $this->assertFalse($result2->isOk());

        // 範囲内
        $result3 = TestPositiveDecimalValue::tryFrom(new Number('0.01'));
        $this->assertTrue($result3->isOk());

        $result4 = TestPositiveDecimalValue::tryFrom(new Number('1000'));
        $this->assertTrue($result4->isOk());
    }

    #[Test]
    public function isZero関数は正の小数値に対して常にfalseを返す(): void
    {
        // 正の値はゼロでないのでfalse
        $positiveValue = TestPositiveDecimalValue::from(new Number('10.50'));
        $this->assertFalse($positiveValue->isZero());

        // 仮にゼロを作れたとしても、テストのために
        try {
            $zero = TestPositiveDecimalValue::from(new Number('0.00'));
            $this->assertFalse($zero->isZero());
        } catch (Throwable $e) {
            // 例外が発生する場合はスキップ
            $this->markTestSkipped('ゼロの値を持つPositiveDecimalValueは作成できません');
        }
    }

    #[Test]
    public function 文字列表現のテスト(): void
    {
        $value1 = TestPositiveDecimalValue::from(new Number('123.45'));
        $this->assertEquals('123.45', (string)$value1);

        $value2 = TestPositiveDecimalValue::from(new Number('100'));
        $this->assertEquals('100', (string)$value2);
    }

    #[Test]
    public function シリアライズとデシリアライズのテスト(): void
    {
        $original = TestPositiveDecimalValue::from(new Number('123.45'));
        $serialized = serialize($original);

        /** @var TestPositiveDecimalValue */
        $unserialized = unserialize($serialized);

        $this->assertEquals((string)$original->value, (string)$unserialized->value);
        $this->assertTrue($original->equals($unserialized));
    }

    #[Test]
    public function 複合的な算術演算のテスト(): void
    {
        $value1 = TestPositiveDecimalValue::from(new Number('10.50'));
        $value2 = TestPositiveDecimalValue::from(new Number('20.25'));
        $value3 = TestPositiveDecimalValue::from(new Number('2.00'));

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
        $value = TestPositiveDecimalValue::from(new Number('10.50'));

        // (((10.50 + 5.25) - 3.00) * 2.00) = ((15.75 - 3.00) * 2.00) = (12.75 * 2.00) = 25.50
        $result = $value
            ->add(TestPositiveDecimalValue::from(new Number('5.25')))
            ->sub(TestPositiveDecimalValue::from(new Number('3.00')))
            ->mul(TestPositiveDecimalValue::from(new Number('2.00')));

        $this->assertEquals('25.5000', (string)$result->value);
    }
}
