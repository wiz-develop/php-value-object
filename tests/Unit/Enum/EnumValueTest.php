<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use WizDevelop\PhpValueObject\Enum\EnumValueFactory;
use WizDevelop\PhpValueObject\Enum\EnumValueObjectDefault;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\Examples\Enum\TestEnumValue;
use WizDevelop\PhpValueObject\Tests\TestCase;

/**
 * 列挙型(Enum)値オブジェクトのテスト
 */
#[TestDox('列挙型(Enum)値オブジェクトのテスト')]
#[Group('EnumValue')]
#[CoversClass(EnumValueFactory::class)]
#[CoversClass(EnumValueObjectDefault::class)]
#[CoversClass(TestEnumValue::class)]
final class EnumValueTest extends TestCase
{
    // ------------------------------------------
    // インスタンス生成と基本機能のテスト
    // ------------------------------------------

    #[Test]
    public function 有効な値でインスタンスが作成できる(): void
    {
        $value1 = TestEnumValue::from('Value1');
        $this->assertSame(TestEnumValue::Value1, $value1);
        $this->assertSame('Value1', $value1->value);

        $value2 = TestEnumValue::from('Value2');
        $this->assertSame(TestEnumValue::Value2, $value2);
        $this->assertSame('Value2', $value2->value);

        $value3 = TestEnumValue::from('Value3');
        $this->assertSame(TestEnumValue::Value3, $value3);
        $this->assertSame('Value3', $value3->value);
    }

    #[Test]
    public function tryFrom2メソッドで有効な値からインスタンスが作成できる(): void
    {
        $result1 = TestEnumValue::tryFrom2('Value1');
        $this->assertTrue($result1->isOk());
        $this->assertSame(TestEnumValue::Value1, $result1->unwrap());

        $result2 = TestEnumValue::tryFrom2('Value2');
        $this->assertTrue($result2->isOk());
        $this->assertSame(TestEnumValue::Value2, $result2->unwrap());

        $result3 = TestEnumValue::tryFrom2('Value3');
        $this->assertTrue($result3->isOk());
        $this->assertSame(TestEnumValue::Value3, $result3->unwrap());
    }

    #[Test]
    public function tryFrom2メソッドで無効な値はエラーになる(): void
    {
        $result = TestEnumValue::tryFrom2('InvalidValue');

        $this->assertFalse($result->isOk());
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertTrue($result->isErr());

        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);
        $this->assertStringContainsString('InvalidValue', $error->getMessage());
    }

    // ------------------------------------------
    // Nullableメソッドのテスト
    // ------------------------------------------

    #[Test]
    public function fromNullable関数でNullを扱える(): void
    {
        // Null値の場合
        $option1 = TestEnumValue::fromNullable(null);
        $this->assertTrue($option1->isNone());

        // 非Null値の場合
        $option2 = TestEnumValue::fromNullable('Value1');
        $this->assertTrue($option2->isSome());
        $this->assertSame(TestEnumValue::Value1, $option2->unwrap());
    }

    #[Test]
    public function tryFromNullable関数でNullを扱える(): void
    {
        // Null値の場合
        $result1 = TestEnumValue::tryFromNullable(null);
        $this->assertTrue($result1->isOk());
        $this->assertTrue($result1->unwrap()->isNone());

        // 有効な非Null値の場合
        $result2 = TestEnumValue::tryFromNullable('Value1');
        $this->assertTrue($result2->isOk());
        $this->assertTrue($result2->unwrap()->isSome());
        $this->assertSame(TestEnumValue::Value1, $result2->unwrap()->unwrap());

        // 無効な非Null値の場合
        $result3 = TestEnumValue::tryFromNullable('InvalidValue');
        $this->assertTrue($result3->isErr());
        $this->assertInstanceOf(ValueObjectError::class, $result3->unwrapErr());
    }

    // ------------------------------------------
    // 値オブジェクトの共通機能テスト
    // ------------------------------------------

    #[Test]
    public function equals関数で同値性の比較ができる(): void
    {
        $value1a = TestEnumValue::from('Value1');
        $value1b = TestEnumValue::from('Value1');
        $value2 = TestEnumValue::from('Value2');

        $this->assertTrue($value1a->equals($value1b));
        $this->assertTrue($value1b->equals($value1a));
        $this->assertFalse($value1a->equals($value2)); // @phpstan-ignore argument.type
        $this->assertFalse($value2->equals($value1a)); // @phpstan-ignore argument.type
    }

    #[Test]
    public function jsonSerializeメソッドは値を返す(): void
    {
        $value1 = TestEnumValue::from('Value1');
        $value2 = TestEnumValue::from('Value2');

        $this->assertSame('Value1', $value1->jsonSerialize());
        $this->assertSame('Value2', $value2->jsonSerialize());

        $jsonValue1 = json_encode($value1);
        $jsonValue2 = json_encode($value2);

        $this->assertSame('"Value1"', $jsonValue1);
        $this->assertSame('"Value2"', $jsonValue2);
    }

    // ------------------------------------------
    // PHPの列挙型の基本機能の確認
    // ------------------------------------------

    #[Test]
    public function 列挙型のcasesメソッドが正常に動作する(): void
    {
        $cases = TestEnumValue::cases();

        $this->assertCount(3, $cases);
        $this->assertSame(TestEnumValue::Value1, $cases[0]);
        $this->assertSame(TestEnumValue::Value2, $cases[1]);
        $this->assertSame(TestEnumValue::Value3, $cases[2]);
    }

    #[Test]
    public function 列挙型のtryFromメソッドが正常に動作する(): void
    {
        $value1 = TestEnumValue::tryFrom('Value1');
        $this->assertSame(TestEnumValue::Value1, $value1);

        $invalidValue = TestEnumValue::tryFrom('InvalidValue');
        $this->assertNull($invalidValue); // @phpstan-ignore method.alreadyNarrowedType
    }
}
