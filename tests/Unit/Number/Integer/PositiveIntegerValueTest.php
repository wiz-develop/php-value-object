<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number\Integer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\Examples\Number\Integer\TestPositiveIntegerValue;
use WizDevelop\PhpValueObject\Examples\Number\Integer\TestZeroAllowedPositiveIntegerValue;
use WizDevelop\PhpValueObject\Number\Integer\PositiveIntegerValue;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * PositiveIntegerValue抽象クラスのテスト
 */
#[TestDox('PositiveIntegerValue抽象クラスのテスト')]
#[CoversClass(PositiveIntegerValue::class)]
#[CoversClass(TestPositiveIntegerValue::class)]
#[CoversClass(TestZeroAllowedPositiveIntegerValue::class)]
final class PositiveIntegerValueTest extends TestCase
{
    #[Test]
    public function 正の値でインスタンスが作成できる(): void
    {
        $positiveInteger = TestPositiveIntegerValue::from(100);
        $this->assertEquals(100, $positiveInteger->value());
    }

    #[Test]
    public function ゼロは許可されていない場合はエラーになる(): void
    {
        $result = TestPositiveIntegerValue::tryFrom(0);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // エラーメッセージに「正の整数」が含まれるか
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('正の整数', $errorMessage);
    }

    #[Test]
    public function 負の値はエラーになる(): void
    {
        $result = TestPositiveIntegerValue::tryFrom(-1);
        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());

        // エラーメッセージに「正の整数」が含まれるか
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('正の整数', $errorMessage);
    }

    #[Test]
    public function isZeroAllowed関数が適切に動作する(): void
    {
        // 標準の実装ではゼロは許容しない
        $this->assertFalse(TestPositiveIntegerValue::includeZero());

        // ゼロを許容するよう設定された実装
        $this->assertTrue(TestZeroAllowedPositiveIntegerValue::includeZero());
    }

    #[Test]
    public function min関数が適切な最小値を返す(): void
    {
        // ゼロを許容しない場合は1
        $this->assertEquals(1, TestPositiveIntegerValue::min());

        // ゼロを許容する場合は0
        $this->assertEquals(0, TestZeroAllowedPositiveIntegerValue::min());
    }

    /**
     * @return array<string, array{0: int, 1: bool, 2: bool}>
     */
    public static function 境界値のテストデータを提供(): array
    {
        return [
            'ゼロ_許容しない' => [0, false, false],
            'ゼロ_許容する' => [0, true, true],
            '1_許容しない' => [1, false, true],
            '1_許容する' => [1, true, true],
            '-1_許容しない' => [-1, false, false],
            '-1_許容する' => [-1, true, false],
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
            ? TestZeroAllowedPositiveIntegerValue::class
            : TestPositiveIntegerValue::class;

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
    public function isPositive関数で有効な値かどうかを判定できる(): void
    {
        // ゼロを許容しない場合
        $this->assertTrue(TestPositiveIntegerValue::isPositive(10)->isOk());
        $this->assertFalse(TestPositiveIntegerValue::isPositive(0)->isOk());
        $this->assertFalse(TestPositiveIntegerValue::isPositive(-10)->isOk());

        // ゼロを許容する場合
        $this->assertTrue(TestZeroAllowedPositiveIntegerValue::isPositive(10)->isOk());
        $this->assertTrue(TestZeroAllowedPositiveIntegerValue::isPositive(0)->isOk());
        $this->assertFalse(TestZeroAllowedPositiveIntegerValue::isPositive(-10)->isOk());
    }
}
