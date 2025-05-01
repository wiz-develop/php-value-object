<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Collection;

use BadMethodCallException;
use BcMath\Number;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Stringable;
use WizDevelop\PhpValueObject\Collection\ArrayList;
use WizDevelop\PhpValueObject\Collection\Exception\CollectionNotFoundException;
use WizDevelop\PhpValueObject\Collection\Exception\MultipleCollectionsFoundException;
use WizDevelop\PhpValueObject\Collection\Map;
use WizDevelop\PhpValueObject\Collection\Pair;
use WizDevelop\PhpValueObject\Number\DecimalValue;
use WizDevelop\PhpValueObject\Number\IntegerValue;
use WizDevelop\PhpValueObject\String\StringValue;

#[TestDox('Mapクラスのテスト')]
#[CoversClass(Map::class)]
final class MapTest extends TestCase
{
    /**
     * @return array<string, array{array<mixed>}>
     */
    public static function 様々な要素を持つPairの配列を提供(): array
    {
        return [
            'プリミティブ値のPair' => [[
                Pair::of('key1', 1),
                Pair::of('key2', 2),
                Pair::of('key3', 3),
            ]],
            '文字列値のPair' => [[
                Pair::of('name1', 'Alice'),
                Pair::of('name2', 'Bob'),
                Pair::of('name3', 'Charlie'),
            ]],
            '数値キーのPair' => [[
                Pair::of(1, 'value1'),
                Pair::of(2, 'value2'),
                Pair::of(3, 'value3'),
            ]],
            '混合型のPair' => [[
                Pair::of('key1', 1),
                Pair::of('key2', 'string'),
                Pair::of('key3', true),
                Pair::of('key4', 3.14),
            ]],
            '空の配列' => [[]],
        ];
    }

    /**
     * @return array<string, array{array<mixed>}>
     */
    public static function 独自クラスを含むPairの配列を提供(): array
    {
        return [
            'StringValueキーのPair' => [[
                Pair::of(StringValue::from('key1'), 'value1'),
                Pair::of(StringValue::from('key2'), 'value2'),
                Pair::of(StringValue::from('key3'), 'value3'),
            ]],
            'IntegerValueキーのPair' => [[
                Pair::of(IntegerValue::from(1), 'value1'),
                Pair::of(IntegerValue::from(2), 'value2'),
                Pair::of(IntegerValue::from(3), 'value3'),
            ]],
            'StringValue値のPair' => [[
                Pair::of('key1', StringValue::from('value1')),
                Pair::of('key2', StringValue::from('value2')),
                Pair::of('key3', StringValue::from('value3')),
            ]],
            'IntegerValue値のPair' => [[
                Pair::of('key1', IntegerValue::from(1)),
                Pair::of('key2', IntegerValue::from(2)),
                Pair::of('key3', IntegerValue::from(3)),
            ]],
            'DecimalValue値のPair' => [[
                Pair::of('key1', DecimalValue::from(new Number('1.5'))),
                Pair::of('key2', DecimalValue::from(new Number('2.5'))),
                Pair::of('key3', DecimalValue::from(new Number('3.5'))),
            ]],
            '混合ValueObjectのPair' => [[
                Pair::of(StringValue::from('key1'), IntegerValue::from(1)),
                Pair::of(IntegerValue::from(2), StringValue::from('value2')),
                Pair::of(StringValue::from('key3'), DecimalValue::from(new Number('3.5'))),
            ]],
        ];
    }

    /**
     * @param Pair<mixed,mixed>[] $pairs
     */
    #[Test]
    #[DataProvider('様々な要素を持つPairの配列を提供')]
    public function from静的メソッドでインスタンスが作成できる(array $pairs): void
    {
        $collection = Map::from(...$pairs);

        $this->assertInstanceOf(Map::class, $collection);

        // toArray()で得られる配列は、キーと値のマッピングになっている
        $expected = [];
        foreach ($pairs as $pair) {
            $expected[$pair->key] = $pair->value;
        }

        $this->assertEquals($expected, $collection->toArray());
    }

    /**
     * @param Pair<mixed,mixed>[] $pairs
     */
    #[Test]
    #[DataProvider('独自クラスを含むPairの配列を提供')]
    public function 独自クラスを含むPairのコレクションが作成できる(array $pairs): void
    {
        $collection = Map::from(...$pairs);

        $this->assertInstanceOf(Map::class, $collection);

        $collection->toArray();

        // キーが独自クラスの場合でもtoArray()で正しくマッピングされる
        $expected = [];
        foreach ($pairs as $pair) {
            $key = match(true) {
                is_int($pair->key) => $pair->key,
                is_string($pair->key) => $pair->key,
                $pair->key instanceof Stringable => (string)$pair->key,
                default => throw new BadMethodCallException('The key must be an integer or string or Stringable.'),
            };
            $expected[$key] = $pair->value;
        }

        $this->assertEquals($expected, $collection->toArray());
    }

    #[Test]
    public function empty静的メソッドで空のコレクションが作成できる(): void
    {
        $collection = Map::empty();

        $this->assertInstanceOf(Map::class, $collection);
        $this->assertEmpty($collection->toArray());
        $this->assertEquals(0, $collection->count());
    }

    #[Test]
    public function make静的メソッドで様々なイテラブルからコレクションが作成できる(): void
    {
        // 連想配列から作成
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        $collection1 = Map::make($array);
        $this->assertEquals($array, $collection1->toArray());

        // ジェネレーターから作成
        $generator = (static function () {
            yield 'x' => 10;
            yield 'y' => 20;
            yield 'z' => 30;
        })();
        $collection2 = Map::make($generator);
        $this->assertEquals(['x' => 10, 'y' => 20, 'z' => 30], $collection2->toArray());

        // 別のMapから作成
        $original = Map::make(['p' => 100, 'q' => 200]);
        $collection3 = Map::make($original);
        $this->assertEquals(['p' => 100, 'q' => 200], $collection3->toArray());
    }

    /**
     * @param Pair<mixed,mixed>[] $pairs
     */
    #[Test]
    #[DataProvider('様々な要素を持つPairの配列を提供')]
    public function tryFrom静的メソッドで有効なPair配列から成功結果が取得できる(array $pairs): void
    {
        $result = Map::tryFrom(...$pairs);

        $this->assertTrue($result->isOk());
        $collection = $result->unwrap();
        $this->assertInstanceOf(Map::class, $collection);

        // toArray()で得られる配列は、キーと値のマッピングになっている
        $expected = [];
        foreach ($pairs as $pair) {
            $expected[$pair->key] = $pair->value;
        }

        $this->assertEquals($expected, $collection->toArray());
    }

    /**
     * @param Pair<mixed,mixed>[] $pairs
     */
    #[Test]
    #[DataProvider('独自クラスを含むPairの配列を提供')]
    public function tryFrom静的メソッドで独自クラスを含むPair配列から成功結果が取得できる(array $pairs): void
    {
        $result = Map::tryFrom(...$pairs);

        $this->assertTrue($result->isOk());
        $collection = $result->unwrap();
        $this->assertInstanceOf(Map::class, $collection);

        // キーが独自クラスの場合でもtoArray()で正しくマッピングされる
        $expected = [];
        foreach ($pairs as $pair) {
            $key = match(true) {
                is_int($pair->key) => $pair->key,
                is_string($pair->key) => $pair->key,
                $pair->key instanceof Stringable => (string)$pair->key,
                default => throw new BadMethodCallException('The key must be an integer or string or Stringable.'),
            };
            $expected[$key] = $pair->value;
        }

        $this->assertEquals($expected, $collection->toArray());
    }

    #[Test]
    public function put関数でキーと値を追加したコレクションが取得できる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2]);

        $newCollection = $collection->put('c', 3);
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $newCollection->toArray());

        // 既存のキーを上書き
        $updatedCollection = $collection->put('a', 10);
        $this->assertEquals(['a' => 10, 'b' => 2], $updatedCollection->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals(['a' => 1, 'b' => 2], $collection->toArray());
    }

    #[Test]
    public function putAll関数で複数のキーと値を追加したコレクションが取得できる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2]);

        $newCollection = $collection->putAll(['c' => 3, 'd' => 4]);
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], $newCollection->toArray());

        // 既存のキーを上書き
        $updatedCollection = $collection->putAll(['a' => 10, 'c' => 3]);
        $this->assertEquals(['a' => 10, 'b' => 2, 'c' => 3], $updatedCollection->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals(['a' => 1, 'b' => 2], $collection->toArray());
    }

    #[Test]
    public function get関数でキーに対応する値が取得できる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertTrue($collection->get('a')->isSome());
        $this->assertEquals(1, $collection->get('a')->unwrap());

        $this->assertTrue($collection->get('b')->isSome());
        $this->assertEquals(2, $collection->get('b')->unwrap());

        $this->assertTrue($collection->get('x')->isNone()); // 存在しないキー

        $this->assertTrue($collection->get('x', 'default')->isSome()); // デフォルト値
        $this->assertEquals('default', $collection->get('x', 'default')->unwrap());
    }

    #[Test]
    public function has関数でキーの存在確認ができる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2]);

        $this->assertTrue($collection->has('a'));
        $this->assertTrue($collection->has('b'));
        $this->assertFalse($collection->has('c'));

        // ValueObjectのキー
        $keyObj = StringValue::from('test');
        $collection2 = Map::from(Pair::of($keyObj, 'value'));
        $this->assertTrue($collection2->has($keyObj));
        $this->assertTrue($collection2->has(StringValue::from('test'))); // 同じ値の別インスタンス
        $this->assertFalse($collection2->has(StringValue::from('other')));
    }

    #[Test]
    public function first関数で先頭のPairが取得できる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3]);

        $firstOption = $collection->first();
        $this->assertTrue($firstOption->isSome());
        $firstPair = $firstOption->unwrap();
        $this->assertInstanceOf(Pair::class, $firstPair);
        $this->assertEquals('a', $firstPair->key);
        $this->assertEquals(1, $firstPair->value);

        // クロージャによるフィルタリング
        $filteredOption = $collection->first(static fn ($value) => $value > 1);
        $this->assertTrue($filteredOption->isSome());
        $filteredPair = $filteredOption->unwrap();
        $this->assertEquals('b', $filteredPair->key);
        $this->assertEquals(2, $filteredPair->value);

        // 条件に合致するものがない場合
        $defaultOption = $collection->first(static fn ($value) => $value > 10, 'default');
        $this->assertTrue($defaultOption->isSome());
        $this->assertEquals('default', $defaultOption->unwrap());

        // 空のコレクション
        $emptyCollection = Map::empty();
        $this->assertTrue($emptyCollection->first()->isNone());
    }

    #[Test]
    public function firstOrFail関数で先頭のPairが取得できる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3]);

        $firstPair = $collection->firstOrFail();
        $this->assertInstanceOf(Pair::class, $firstPair);
        $this->assertEquals('a', $firstPair->key);
        $this->assertEquals(1, $firstPair->value);

        // クロージャによるフィルタリング
        $filteredPair = $collection->firstOrFail(static fn ($value) => $value > 1);
        $this->assertEquals('b', $filteredPair->key);
        $this->assertEquals(2, $filteredPair->value);
    }

    #[Test]
    public function firstOrFail関数で要素が見つからない場合は例外が発生する(): void
    {
        $this->expectException(CollectionNotFoundException::class);

        $emptyCollection = Map::empty();
        $emptyCollection->firstOrFail();
    }

    #[Test]
    public function last関数で末尾のPairが取得できる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3]);

        $lastOption = $collection->last();
        $this->assertTrue($lastOption->isSome());
        $lastPair = $lastOption->unwrap();
        $this->assertInstanceOf(Pair::class, $lastPair);
        $this->assertEquals('c', $lastPair->key);
        $this->assertEquals(3, $lastPair->value);

        // クロージャによるフィルタリング
        $filteredOption = $collection->last(static fn ($value) => $value < 3);
        $this->assertTrue($filteredOption->isSome());
        $filteredPair = $filteredOption->unwrap();
        $this->assertEquals('b', $filteredPair->key);
        $this->assertEquals(2, $filteredPair->value);

        // 条件に合致するものがない場合
        $defaultOption = $collection->last(static fn ($value) => $value > 10, 'default');
        $this->assertTrue($defaultOption->isSome());
        $this->assertEquals('default', $defaultOption->unwrap());

        // 空のコレクション
        $emptyCollection = Map::empty();
        $this->assertTrue($emptyCollection->last()->isNone());
    }

    #[Test]
    public function lastOrFail関数で末尾のPairが取得できる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3]);

        $lastPair = $collection->lastOrFail();
        $this->assertInstanceOf(Pair::class, $lastPair);
        $this->assertEquals('c', $lastPair->key);
        $this->assertEquals(3, $lastPair->value);

        // クロージャによるフィルタリング
        $filteredPair = $collection->lastOrFail(static fn ($value) => $value < 3);
        $this->assertEquals('b', $filteredPair->key);
        $this->assertEquals(2, $filteredPair->value);
    }

    #[Test]
    public function lastOrFail関数で要素が見つからない場合は例外が発生する(): void
    {
        $this->expectException(CollectionNotFoundException::class);

        $emptyCollection = Map::empty();
        $emptyCollection->lastOrFail();
    }

    #[Test]
    public function sole関数で条件に合うPairが1つだけ存在する場合にそのPairが取得できる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3]);

        $pair = $collection->sole(static fn ($value) => $value === 2);
        $this->assertInstanceOf(Pair::class, $pair);
        $this->assertEquals('b', $pair->key);
        $this->assertEquals(2, $pair->value);
    }

    #[Test]
    public function sole関数で条件に合うPairがない場合は例外が発生する(): void
    {
        $this->expectException(CollectionNotFoundException::class);

        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3]);
        $collection->sole(static fn ($value) => $value > 10);
    }

    #[Test]
    public function sole関数で条件に合うPairが複数ある場合は例外が発生する(): void
    {
        $this->expectException(MultipleCollectionsFoundException::class);

        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3]);
        $collection->sole(static fn ($value) => $value > 1);
    }

    #[Test]
    public function slice関数で部分コレクションが取得できる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);

        // インデックス0から2要素
        $slice1 = $collection->slice(0, 2);
        $this->assertEquals(['a' => 1, 'b' => 2], $slice1->toArray());

        // インデックス2から2要素
        $slice2 = $collection->slice(2, 2);
        $this->assertEquals(['c' => 3, 'd' => 4], $slice2->toArray());

        // インデックス4から末尾まで
        $slice3 = $collection->slice(4);
        $this->assertEquals(['e' => 5], $slice3->toArray());
    }

    #[Test]
    public function reverse関数で要素が逆順になったコレクションが取得できる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3]);
        $reversed = $collection->reverse();

        // キーと値のマッピングは保持されるが、要素の並び順が逆になる
        $this->assertEquals(['c' => 3, 'b' => 2, 'a' => 1], $reversed->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $collection->toArray());
    }

    #[Test]
    public function ArrayAccessインターフェースが実装されている(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3]);

        // offsetExists
        $this->assertTrue(isset($collection['a']));
        $this->assertFalse(isset($collection['x']));

        // offsetGet
        $this->assertEquals(1, $collection['a']);
        $this->assertEquals(2, $collection['b']);

        // 存在しないキーのアクセスは例外発生
        $this->expectException(OutOfBoundsException::class);
        $value = $collection['x'];
    }

    #[Test]
    public function イミュータブルであることを確認するためオフセットの設定はできない(): void
    {
        $this->expectException(BadMethodCallException::class);

        $collection = Map::make(['a' => 1]);
        $collection['b'] = 2; // offsetSet
    }

    #[Test]
    public function イミュータブルであることを確認するためオフセットの削除はできない(): void
    {
        $this->expectException(BadMethodCallException::class);

        $collection = Map::make(['a' => 1]);
        unset($collection['a']); // offsetUnset
    }

    #[Test]
    public function merge関数で別のコレクションと結合したコレクションが取得できる(): void
    {
        $collection1 = Map::make(['a' => 1, 'b' => 2]);
        $collection2 = Map::make(['c' => 3, 'd' => 4]);

        $mergedCollection = $collection1->merge($collection2);
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], $mergedCollection->toArray());

        // キーが重複する場合は後から結合するコレクションの値で上書き
        $collection3 = Map::make(['a' => 10, 'e' => 5]);
        $overwrittenCollection = $collection1->merge($collection3);
        $this->assertEquals(['a' => 10, 'b' => 2, 'e' => 5], $overwrittenCollection->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals(['a' => 1, 'b' => 2], $collection1->toArray());
    }

    #[Test]
    public function map関数で各値を変換したコレクションが取得できる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3]);

        $mappedCollection = $collection->map(static fn ($value, $key) => $value * 2);
        $this->assertEquals(['a' => 2, 'b' => 4, 'c' => 6], $mappedCollection->toArray());

        // キーを使った変換
        $mappedWithKey = $collection->map(static fn ($value, $key) => $key . $value);
        $this->assertEquals(['a' => 'a1', 'b' => 'b2', 'c' => 'c3'], $mappedWithKey->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $collection->toArray());
    }

    #[Test]
    public function mapStrict関数で型情報を保持したまま各値を変換できる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3]);

        $mappedCollection = $collection->mapStrict(static fn ($value, $key) => $value * 2);
        $this->assertEquals(['a' => 2, 'b' => 4, 'c' => 6], $mappedCollection->toArray());

        // 同じ型のインスタンスである
        $this->assertInstanceOf(Map::class, $mappedCollection);
    }

    #[Test]
    public function filter関数で条件に合う要素のみのコレクションが取得できる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);

        $filteredCollection = $collection->filter(static fn ($value) => $value % 2 === 0);
        $this->assertEquals(['b' => 2, 'd' => 4], $filteredCollection->toArray());

        // キーを使ったフィルタリング
        $filteredByKey = $collection->filter(static fn ($value, $key) => $key === 'a' || $key === 'c');
        $this->assertEquals(['a' => 1, 'c' => 3], $filteredByKey->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5], $collection->toArray());
    }

    #[Test]
    public function reject関数で条件に合わない要素のみのコレクションが取得できる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);

        $rejectedCollection = $collection->reject(static fn ($value) => $value % 2 === 0);
        $this->assertEquals(['a' => 1, 'c' => 3, 'e' => 5], $rejectedCollection->toArray());

        // キーを使った拒否
        $rejectedByKey = $collection->reject(static fn ($value, $key) => $key === 'a' || $key === 'c');
        $this->assertEquals(['b' => 2, 'd' => 4, 'e' => 5], $rejectedByKey->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5], $collection->toArray());
    }

    #[Test]
    public function reduce関数で要素を集約できる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);

        $sum = $collection->reduce(static fn ($carry, $value) => $carry + $value, 0);
        $this->assertEquals(15, $sum);

        // キーを使った集約
        $concatenated = $collection->reduce(static fn ($carry, $value, $key) => $carry . $key . $value, '');
        $this->assertEquals('a1b2c3d4e5', $concatenated);
    }

    #[Test]
    public function sort関数で要素をソートしたコレクションが取得できる(): void
    {
        $collection = Map::make(['c' => 3, 'a' => 1, 'e' => 5, 'b' => 2, 'd' => 4]);

        // デフォルトソート（値で昇順）
        $sortedCollection = $collection->sort();
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5], $sortedCollection->toArray());

        // カスタムソート（値で降順）
        $customSortedCollection = $collection->sort(static fn ($a, $b) => $b <=> $a);
        $this->assertEquals(['e' => 5, 'd' => 4, 'c' => 3, 'b' => 2, 'a' => 1], $customSortedCollection->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals(['c' => 3, 'a' => 1, 'e' => 5, 'b' => 2, 'd' => 4], $collection->toArray());
    }

    #[Test]
    public function values関数で値だけを取り出したArrayListが取得できる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3]);

        $values = $collection->values();
        $this->assertInstanceOf(ArrayList::class, $values);
        $this->assertEquals([1, 2, 3], $values->toArray());
    }

    #[Test]
    public function keys関数でキーだけを取り出したArrayListが取得できる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3]);

        $keys = $collection->keys();
        $this->assertInstanceOf(ArrayList::class, $keys);
        $this->assertEquals(['a', 'b', 'c'], $keys->toArray());
    }

    #[Test]
    public function remove関数で指定したキーを削除したコレクションが取得できる(): void
    {
        $collection = Map::make(['a' => 1, 'b' => 2, 'c' => 3]);

        $removedCollection = $collection->remove('b');
        $this->assertEquals(['a' => 1, 'c' => 3], $removedCollection->toArray());

        // 存在しないキーを指定した場合は何も起きない
        $sameCollection = $collection->remove('x');
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $sameCollection->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $collection->toArray());
    }
}
