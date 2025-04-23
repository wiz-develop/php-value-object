<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number\Integer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use WizDevelop\PhpValueObject\Examples\Number\Integer\TestPositiveIntegerValue;
use WizDevelop\PhpValueObject\Number\Integer\PositiveIntegerValue;
use WizDevelop\PhpValueObject\Number\NumberValueError;
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

    /**
     * @return array<string, array{0: int, 1: bool}>
     */
    public static function 境界値のテストデータを提供(): array
    {
        return [
            'ゼロ_許容しない' => [0, false],
            'ゼロ_許容する' => [0, false],
            '1_許容しない' => [1, true],
            '1_許容する' => [1, true],
            '-1_許容しない' => [-1, false],
            '-1_許容する' => [-1, false],
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
        $result = TestPositiveIntegerValue::tryFrom($value);

        if ($shouldBeOk) {
            $this->assertTrue($result->isOk(), "値:{$value} は成功するべき");
            $this->assertEquals($value, $result->unwrap()->value);
        } else {
            $this->assertFalse($result->isOk(), "値:{$value} は失敗するべき");
            $this->assertInstanceOf(NumberValueError::class, $result->unwrapErr());
        }
    }
}
