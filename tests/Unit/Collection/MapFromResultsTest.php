<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Collection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Collection\Map;
use WizDevelop\PhpValueObject\Collection\Pair;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\Number\IntegerValue;
use WizDevelop\PhpValueObject\String\StringValue;

#[TestDox('Map::tryFromResults メソッドのテスト')]
#[CoversClass(Map::class)]
final class MapFromResultsTest extends TestCase
{
    #[Test]
    public function 成功したResultのPairから正常にMapが作成できる(): void
    {
        // 成功したResultのキーと値を持つPairを作成
        $pairs = [
            Pair::of(Result\ok('a'), Result\ok(1)),
            Pair::of(Result\ok('b'), Result\ok(2)),
            Pair::of(Result\ok('c'), Result\ok(3)),
        ];

        // tryFromResults メソッドを呼び出す
        $result = Map::tryFromResults(...$pairs);

        // 結果が成功であることを確認
        $this->assertTrue($result->isOk());

        // 正しく要素が取り出せることを確認
        $map = $result->unwrap();
        $this->assertInstanceOf(Map::class, $map);
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $map->toArray());
    }

    #[Test]
    public function 成功したバリューオブジェクトのResultのPairから正常にMapが作成できる(): void
    {
        // 成功したバリューオブジェクトのResultのPairを作成
        $pairs = [
            Pair::of(Result\ok(StringValue::from('key1')), Result\ok(IntegerValue::from(10))),
            Pair::of(Result\ok(StringValue::from('key2')), Result\ok(IntegerValue::from(20))),
            Pair::of(Result\ok(StringValue::from('key3')), Result\ok(IntegerValue::from(30))),
        ];

        // tryFromResults メソッドを呼び出す
        $result = Map::tryFromResults(...$pairs);

        // 結果が成功であることを確認
        $this->assertTrue($result->isOk());

        // 正しく要素が取り出せることを確認
        $map = $result->unwrap();
        $this->assertInstanceOf(Map::class, $map);

        // マップから各要素を取得して検証
        $this->assertCount(3, $map->toArray());

        // キーと値を検証（特殊なキーの処理を考慮）
        $this->assertTrue($map->has(StringValue::from('key1')));
        $this->assertTrue($map->has(StringValue::from('key2')));
        $this->assertTrue($map->has(StringValue::from('key3')));

        // StringValueのキーで値を取得
        $value1 = $map->get(StringValue::from('key1'))->unwrap();
        $value2 = $map->get(StringValue::from('key2'))->unwrap();
        $value3 = $map->get(StringValue::from('key3'))->unwrap();

        $this->assertInstanceOf(IntegerValue::class, $value1);
        $this->assertInstanceOf(IntegerValue::class, $value2);
        $this->assertInstanceOf(IntegerValue::class, $value3);
        $this->assertEquals(10, $value1->value);
        $this->assertEquals(20, $value2->value);
        $this->assertEquals(30, $value3->value);
    }

    #[Test]
    public function 異なる型のバリューオブジェクトのResultのPairから正常にMapが作成できる(): void
    {
        // 異なる型のバリューオブジェクトのResultのPairを作成
        $pairs = [
            Pair::of(Result\ok(IntegerValue::from(1)), Result\ok(StringValue::from('value1'))),
            Pair::of(Result\ok(StringValue::from('key2')), Result\ok(IntegerValue::from(2))),
            Pair::of(Result\ok(IntegerValue::from(3)), Result\ok(StringValue::from('value3'))),
        ];

        // tryFromResults メソッドを呼び出す
        $result = Map::tryFromResults(...$pairs);

        // 結果が成功であることを確認
        $this->assertTrue($result->isOk());

        // 正しく要素が取り出せることを確認
        $map = $result->unwrap();
        $this->assertInstanceOf(Map::class, $map);

        // 各キーの存在を確認
        $this->assertTrue($map->has(IntegerValue::from(1)));
        $this->assertTrue($map->has(StringValue::from('key2')));
        $this->assertTrue($map->has(IntegerValue::from(3)));

        // キーを使って値を取得して検証
        $value1 = $map->get(IntegerValue::from(1))->unwrap();
        $value2 = $map->get(StringValue::from('key2'))->unwrap();
        $value3 = $map->get(IntegerValue::from(3))->unwrap();

        $this->assertInstanceOf(StringValue::class, $value1);
        $this->assertInstanceOf(IntegerValue::class, $value2);
        $this->assertInstanceOf(StringValue::class, $value3);
        $this->assertEquals('value1', $value1->value);
        $this->assertEquals(2, $value2->value);
        $this->assertEquals('value3', $value3->value);
    }

    #[Test]
    public function キーに失敗したResultが含まれる場合はエラーが返される(): void
    {
        // キーに失敗したResultを含むPairの配列を作成
        $pairs = [
            Pair::of(Result\ok('a'), Result\ok(1)),
            Pair::of(Result\err(ValueObjectError::general()->invalid('キーエラー')), Result\ok(2)),
            Pair::of(Result\ok('c'), Result\ok(3)),
        ];

        // tryFromResults メソッドを呼び出す
        $result = Map::tryFromResults(...$pairs);

        // 結果が失敗であることを確認
        $this->assertTrue($result->isErr());

        // エラー情報を検証
        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);
        $this->assertStringContainsString('無効な要素が含まれています', $error->getMessage());
        $this->assertStringContainsString('キーエラー', $error->serialize());
    }

    #[Test]
    public function 値に失敗したResultが含まれる場合はエラーが返される(): void
    {
        // 値に失敗したResultを含むPairの配列を作成
        $pairs = [
            Pair::of(Result\ok('a'), Result\ok(1)),
            Pair::of(Result\ok('b'), Result\err(ValueObjectError::general()->invalid('値エラー'))),
            Pair::of(Result\ok('c'), Result\ok(3)),
        ];

        // tryFromResults メソッドを呼び出す
        $result = Map::tryFromResults(...$pairs);

        // 結果が失敗であることを確認
        $this->assertTrue($result->isErr());

        // エラー情報を検証
        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);
        $this->assertStringContainsString('無効な要素が含まれています', $error->getMessage());
        $this->assertStringContainsString('値エラー', $error->serialize());
    }

    #[Test]
    public function キーと値の両方に失敗したResultが含まれる場合は全てのエラーが集約される(): void
    {
        // キーと値の両方に失敗したResultを含むPairの配列を作成
        $pairs = [
            Pair::of(Result\ok('a'), Result\ok(1)),
            Pair::of(Result\err(ValueObjectError::general()->invalid('キーエラー1')), Result\ok(2)),
            Pair::of(Result\ok('c'), Result\err(ValueObjectError::general()->invalid('値エラー1'))),
            Pair::of(Result\err(ValueObjectError::general()->invalid('キーエラー2')), Result\err(ValueObjectError::general()->invalid('値エラー2'))),
        ];

        // tryFromResults メソッドを呼び出す
        $result = Map::tryFromResults(...$pairs);

        // 結果が失敗であることを確認
        $this->assertTrue($result->isErr());

        // エラー情報を検証
        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);
        $this->assertStringContainsString('無効な要素が含まれています', $error->getMessage());
        $this->assertStringContainsString('キーエラー1', $error->serialize());
        $this->assertStringContainsString('値エラー1', $error->serialize());
        $this->assertStringContainsString('キーエラー2', $error->serialize());
        $this->assertStringContainsString('値エラー2', $error->serialize());
    }

    #[Test]
    public function 空のPair配列から空のMapが作成できる(): void
    {
        // 空の配列を作成
        $pairs = [];

        // tryFromResults メソッドを呼び出す
        $result = Map::tryFromResults(...$pairs);

        // 結果が成功であることを確認
        $this->assertTrue($result->isOk());

        // 空のマップであることを確認
        $map = $result->unwrap();
        $this->assertInstanceOf(Map::class, $map);
        $this->assertEmpty($map->toArray());
        $this->assertEquals(0, $map->count());
    }
}
