<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number\Integer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\Examples\Number\Integer\TestNegativeIntegerValue;
use WizDevelop\PhpValueObject\Examples\Number\Integer\TestZeroAllowedNegativeIntegerValue;
use WizDevelop\PhpValueObject\Number\Integer\NegativeIntegerValue;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * NegativeIntegerValue抽象クラスのテスト
 */
#[TestDox('NegativeIntegerValue抽象クラスのテスト')]
#[CoversClass(NegativeIntegerValue::class)]
#[CoversClass(TestNegativeIntegerValue::class)]
#[CoversClass(TestZeroAllowedNegativeIntegerValue::class)]
final class NegativeIntegerValueTest extends TestCase
{
    #[Test]
    public function 負の値でインスタンスが作成できる(): void
    {
        $negativeInteger = TestNegativeIntegerValue::from(-100);
        $this->assertEquals(-100, $negativeInteger->value());
    }

    #[Test]
    public function ゼロは許可されていない場合はエラーになる(): void
    {
        $result = TestNegativeIntegerValue::tryFrom(0);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // エラーメッセージに「負の整数」が含まれるか
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('負の整数', $errorMessage);
    }

    #[Test]
    public function 正の値はエラーになる(): void
    {
        $result = TestNegativeIntegerValue::tryFrom(1);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // エラーメッセージに「負の整数」が含まれるか
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('負の整数', $errorMessage);
    }

    #[Test]
    public function isZeroAllowed関数が適切に動作する(): void
    {
        // 標準の実装ではゼロは許容しない
        $this->assertFalse(TestNegativeIntegerValue::includeZero());

        // ゼロを許容するよう設定された実装
        $this->assertTrue(TestZeroAllowedNegativeIntegerValue::includeZero());
    }

    #[Test]
    public function max関数が適切な最大値を返す(): void
    {
        // ゼロを許容しない場合は-1
        $this->assertEquals(-1, TestNegativeIntegerValue::max());

        // ゼロを許容する場合は0
        $this->assertEquals(0, TestZeroAllowedNegativeIntegerValue::max());
    }

    /**
     * @return array<string, array{0: int, 1: bool, 2: bool}>
     */
    public static function 境界値のテストデータを提供(): array
    {
        return [
            'ゼロ_許容しない' => [0, false, false],
            'ゼロ_許容する' => [0, true, true],
            '-1_許容しない' => [-1, false, true],
            '-1_許容する' => [-1, true, true],
            '1_許容しない' => [1, false, false],
            '1_許容する' => [1, true, false],
        ];
    }

    /**
     * @param int  $value       テスト対象の値
     * @param bool $zeroAllowed ゼロを許容するかどうか
     * @param bool $shouldBeOk  成功するべきかどうか
     */
    #[Test]
    #[DataProvider('境界値のテストデータを提供')]
    public function 境界値テスト(int $value, bool $zeroAllowed, bool $shouldBeOk): void
    {
        $factory = $zeroAllowed
            ? TestZeroAllowedNegativeIntegerValue::class
            : TestNegativeIntegerValue::class;

        $result = $factory::tryFrom($value);

        if ($shouldBeOk) {
            $this->assertTrue($result->isOk(), "値:{$value} ゼロ許容:{$zeroAllowed} は成功するべき");
            $this->assertEquals($value, $result->unwrap()->value());
        } else {
            $this->assertFalse($result->isOk(), "値:{$value} ゼロ許容:{$zeroAllowed} は失敗するべき");
            $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
        }
    }

    #[Test]
    public function isValid関数で有効な値かどうかを判定できる(): void
    {
        // ゼロを許容しない場合
        $this->assertTrue(TestNegativeIntegerValue::isValid(-10)->isOk());
        $this->assertFalse(TestNegativeIntegerValue::isValid(0)->isOk());
        $this->assertFalse(TestNegativeIntegerValue::isValid(10)->isOk());

        // ゼロを許容する場合
        $this->assertTrue(TestZeroAllowedNegativeIntegerValue::isValid(-10)->isOk());
        $this->assertTrue(TestZeroAllowedNegativeIntegerValue::isValid(0)->isOk());
        $this->assertFalse(TestZeroAllowedNegativeIntegerValue::isValid(10)->isOk());
    }
}
