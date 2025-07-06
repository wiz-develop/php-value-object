<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Collection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Collection\ArrayList;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\Number\IntegerValue;
use WizDevelop\PhpValueObject\String\StringValue;

#[TestDox('ArrayList::tryFromResults メソッドのテスト')]
#[CoversClass(ArrayList::class)]
final class ArrayListFromResultsTest extends TestCase
{
    #[Test]
    public function 成功したResultの配列から正常にArrayListが作成できる(): void
    {
        // 成功したResultの配列を作成
        $results = [
            Result\ok(1),
            Result\ok(2),
            Result\ok(3),
        ];

        // tryFromResults メソッドを呼び出す
        $result = ArrayList::tryFromResults($results);

        // 結果が成功であることを確認
        $this->assertTrue($result->isOk());

        // 正しく要素が取り出せることを確認
        $list = $result->unwrap();
        $this->assertInstanceOf(ArrayList::class, $list);
        $this->assertEquals([1, 2, 3], $list->toArray());
    }

    #[Test]
    public function 成功したバリューオブジェクトのResultの配列から正常にArrayListが作成できる(): void
    {
        // 成功したバリューオブジェクトのResultの配列を作成
        $results = [
            IntegerValue::tryFrom(10),
            IntegerValue::tryFrom(20),
            IntegerValue::tryFrom(30),
        ];

        // tryFromResults メソッドを呼び出す
        $result = ArrayList::tryFromResults($results);

        // 結果が成功であることを確認
        $this->assertTrue($result->isOk());

        // 正しく要素が取り出せることを確認
        $list = $result->unwrap();
        $this->assertInstanceOf(ArrayList::class, $list);

        // バリューオブジェクトの値を検証
        $elements = $list->toArray();
        $this->assertCount(3, $elements);
        $this->assertInstanceOf(IntegerValue::class, $elements[0]);
        $this->assertInstanceOf(IntegerValue::class, $elements[1]);
        $this->assertInstanceOf(IntegerValue::class, $elements[2]);
        $this->assertEquals(10, $elements[0]->value);
        $this->assertEquals(20, $elements[1]->value);
        $this->assertEquals(30, $elements[2]->value);
    }

    #[Test]
    public function 異なる型のバリューオブジェクトのResultの配列から正常にArrayListが作成できる(): void
    {
        // 異なる型のバリューオブジェクトのResultの配列を作成
        $results = [
            StringValue::tryFrom('hello'),
            IntegerValue::tryFrom(42),
            StringValue::tryFrom('world'),
        ];

        // tryFromResults メソッドを呼び出す
        $result = ArrayList::tryFromResults($results);

        // 結果が成功であることを確認
        $this->assertTrue($result->isOk());

        // 正しく要素が取り出せることを確認
        $list = $result->unwrap();
        $this->assertInstanceOf(ArrayList::class, $list);

        // バリューオブジェクトの値を検証
        $elements = $list->toArray();
        $this->assertCount(3, $elements);
        $this->assertInstanceOf(StringValue::class, $elements[0]);
        $this->assertInstanceOf(IntegerValue::class, $elements[1]);
        $this->assertInstanceOf(StringValue::class, $elements[2]);
        $this->assertEquals('hello', $elements[0]->value);
        $this->assertEquals(42, $elements[1]->value);
        $this->assertEquals('world', $elements[2]->value);
    }

    #[Test]
    public function 一つでも失敗したResultが含まれる場合はエラーが返される(): void
    {
        // 失敗したResultを1つ含む配列を作成
        $results = [
            Result\ok(1),
            Result\err(ValueObjectError::general()->invalid('テストエラー')),
            Result\ok(3),
        ];

        // tryFromResults メソッドを呼び出す
        $result = ArrayList::tryFromResults($results);

        // 結果が失敗であることを確認
        $this->assertTrue($result->isErr());

        // エラー情報を検証
        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);
        $this->assertStringContainsString('無効な要素が含まれています', $error->getMessage());
        $this->assertStringContainsString('テストエラー', $error->serialize());
    }

    #[Test]
    public function 複数の失敗したResultが含まれる場合は全てのエラーが集約される(): void
    {
        // 複数の失敗したResultを含む配列を作成
        $results = [
            Result\ok(1),
            Result\err(ValueObjectError::general()->invalid('エラー1')),
            Result\ok(3),
            Result\err(ValueObjectError::general()->invalid('エラー2')),
        ];

        // tryFromResults メソッドを呼び出す
        $result = ArrayList::tryFromResults($results);

        // 結果が失敗であることを確認
        $this->assertTrue($result->isErr());

        // エラー情報を検証
        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);
        $this->assertStringContainsString('無効な要素が含まれています', $error->getMessage());
        $this->assertStringContainsString('エラー1', $error->serialize());
        $this->assertStringContainsString('エラー2', $error->serialize());
    }

    #[Test]
    public function 空の配列から空のArrayListが作成できる(): void
    {
        // 空の配列を作成
        $results = [];

        // tryFromResults メソッドを呼び出す
        $result = ArrayList::tryFromResults($results);

        // 結果が成功であることを確認
        $this->assertTrue($result->isOk());

        // 空のリストであることを確認
        $list = $result->unwrap();
        $this->assertInstanceOf(ArrayList::class, $list);
        $this->assertEmpty($list->toArray());
        $this->assertEquals(0, $list->count());
    }

    #[Test]
    public function イテラブルなオブジェクトからArrayListが作成できる(): void
    {
        // イテラブルなオブジェクトを作成（ジェネレータを使用）
        $generator = (static function () {
            yield Result\ok(1);
            yield Result\ok(2);
            yield Result\ok(3);
        })();

        // tryFromResults メソッドを呼び出す
        $result = ArrayList::tryFromResults($generator);

        // 結果が成功であることを確認
        $this->assertTrue($result->isOk());

        // 正しく要素が取り出せることを確認
        $list = $result->unwrap();
        $this->assertInstanceOf(ArrayList::class, $list);
        $this->assertEquals([1, 2, 3], $list->toArray());
    }
}
