<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Collection;

use BadMethodCallException;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\Collection\Exception\CollectionNotFoundException;
use WizDevelop\PhpValueObject\Collection\Exception\MultipleCollectionsFoundException;
use WizDevelop\PhpValueObject\Collection\ListCollection;
use WizDevelop\PhpValueObject\Collection\MapCollection;
use WizDevelop\PhpValueObject\Collection\Pair;

#[TestDox('MapCollectionクラスのテスト')]
#[CoversClass(MapCollection::class)]
#[CoversClass(Pair::class)]
final class MapCollectionTest extends TestCase
{
    #[Test]
    public function 空のコレクションが作成できる(): void
    {
        $collection = MapCollection::empty();

        $this->assertInstanceOf(MapCollection::class, $collection);
        $this->assertCount(0, $collection);
        $this->assertEquals([], $collection->toArray());
    }

    #[Test]
    public function from静的メソッドでコレクションが作成できる(): void
    {
        $pair1 = Pair::of('key1', 'value1');
        $pair2 = Pair::of('key2', 'value2');

        $collection = MapCollection::from($pair1, $pair2);

        $this->assertInstanceOf(MapCollection::class, $collection);
        $this->assertCount(2, $collection);
        $this->assertEquals([
            'key1' => 'value1',
            'key2' => 'value2',
        ], $collection->toArray());
    }

    #[Test]
    public function tryFrom静的メソッドでコレクションが作成できる(): void
    {
        $pair1 = Pair::of('key1', 'value1');
        $pair2 = Pair::of('key2', 'value2');

        $result = MapCollection::tryFrom($pair1, $pair2);

        $this->assertTrue($result->isOk());
        $collection = $result->unwrap();
        $this->assertInstanceOf(MapCollection::class, $collection);
        $this->assertEquals([
            'key1' => 'value1',
            'key2' => 'value2',
        ], $collection->toArray());
    }

    #[Test]
    public function make静的メソッドでコレクションが作成できる(): void
    {
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $collection = MapCollection::make($data);

        $this->assertInstanceOf(MapCollection::class, $collection);
        $this->assertCount(2, $collection);
        $this->assertEquals($data, $collection->toArray());
    }

    #[Test]
    public function iteratorが正しく値を返す(): void
    {
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $collection = MapCollection::make($data);

        $result = [];
        foreach ($collection as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertEquals($data, $result);
    }

    #[Test]
    public function firstメソッドで最初のPairを取得できる(): void
    {
        $pair1 = Pair::of('key1', 'value1');
        $pair2 = Pair::of('key2', 'value2');

        $collection = MapCollection::from($pair1, $pair2);

        $first = $collection->first();
        $this->assertEquals($pair1, $first);
    }

    #[Test]
    public function firstメソッドにクロージャを渡して条件に一致する最初のPairを取得できる(): void
    {
        $pair1 = Pair::of('key1', 'value1');
        $pair2 = Pair::of('key2', 'value2');
        $pair3 = Pair::of('key3', 'value3');

        $collection = MapCollection::from($pair1, $pair2, $pair3);

        $result = $collection->first(static fn ($value, $key) => $key === 'key2');

        $this->assertEquals($pair2, $result);
    }

    #[Test]
    public function firstOrFailメソッドで最初のPairを取得できる(): void
    {
        $pair1 = Pair::of('key1', 'value1');
        $pair2 = Pair::of('key2', 'value2');

        $collection = MapCollection::from($pair1, $pair2);

        $this->assertEquals($pair1, $collection->firstOrFail());
    }

    #[Test]
    public function firstOrFailメソッドで要素がない場合は例外が発生する(): void
    {
        $collection = MapCollection::empty();

        $this->expectException(CollectionNotFoundException::class);
        $collection->firstOrFail();
    }

    #[Test]
    public function lastメソッドで最後のPairを取得できる(): void
    {
        $pair1 = Pair::of('key1', 'value1');
        $pair2 = Pair::of('key2', 'value2');

        $collection = MapCollection::from($pair1, $pair2);

        $this->assertEquals($pair2, $collection->last());
    }

    #[Test]
    public function lastOrFailメソッドで最後のPairを取得できる(): void
    {
        $pair1 = Pair::of('key1', 'value1');
        $pair2 = Pair::of('key2', 'value2');

        $collection = MapCollection::from($pair1, $pair2);

        $this->assertEquals($pair2, $collection->lastOrFail());
    }

    #[Test]
    public function lastOrFailメソッドで要素がない場合は例外が発生する(): void
    {
        $collection = MapCollection::empty();

        $this->expectException(CollectionNotFoundException::class);
        $collection->lastOrFail();
    }

    #[Test]
    public function reverseメソッドでコレクションを逆順にできる(): void
    {
        $pair1 = Pair::of('key1', 'value1');
        $pair2 = Pair::of('key2', 'value2');

        $collection = MapCollection::from($pair1, $pair2);
        $reversed = $collection->reverse();

        $this->assertEquals($pair2, $reversed->first());
        $this->assertEquals($pair1, $reversed->last());
    }

    #[Test]
    public function soleメソッドで唯一のPairを取得できる(): void
    {
        $pair = Pair::of('key', 'value');
        $collection = MapCollection::from($pair);

        $this->assertEquals($pair, $collection->sole());
    }

    #[Test]
    public function soleメソッドで要素がない場合は例外が発生する(): void
    {
        $collection = MapCollection::empty();

        $this->expectException(CollectionNotFoundException::class);
        $collection->sole();
    }

    #[Test]
    public function soleメソッドで複数の要素がある場合は例外が発生する(): void
    {
        $pair1 = Pair::of('key1', 'value1');
        $pair2 = Pair::of('key2', 'value2');

        $collection = MapCollection::from($pair1, $pair2);

        $this->expectException(MultipleCollectionsFoundException::class);
        $collection->sole();
    }

    #[Test]
    public function sliceメソッドでコレクションの一部を取得できる(): void
    {
        $pair1 = Pair::of('key1', 'value1');
        $pair2 = Pair::of('key2', 'value2');
        $pair3 = Pair::of('key3', 'value3');

        $collection = MapCollection::from($pair1, $pair2, $pair3);

        $sliced = $collection->slice(1, 1);

        $this->assertCount(1, $sliced);
        $this->assertEquals($pair2, $sliced->first());
    }

    #[Test]
    public function putメソッドでキーと値のペアを追加できる(): void
    {
        $collection = MapCollection::empty();

        $newCollection = $collection->put('key', 'value');

        $this->assertCount(1, $newCollection);
        $this->assertEquals('value', $newCollection->get('key'));
        // 元のコレクションは変更されていないことを確認
        $this->assertCount(0, $collection);
    }

    #[Test]
    public function putAllメソッドで複数のキーと値のペアを追加できる(): void
    {
        $collection = MapCollection::empty();
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $newCollection = $collection->putAll($data);

        $this->assertCount(2, $newCollection);
        $this->assertEquals('value1', $newCollection->get('key1'));
        $this->assertEquals('value2', $newCollection->get('key2'));
    }

    #[Test]
    public function getメソッドで値を取得できる(): void
    {
        $collection = MapCollection::make([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $this->assertEquals('value1', $collection->get('key1'));
        $this->assertEquals('value2', $collection->get('key2'));
        $this->assertNull($collection->get('key3'));
        $this->assertEquals('default', $collection->get('key3', 'default'));
    }

    #[Test]
    public function mergeメソッドで別のMapCollectionとマージできる(): void
    {
        $collection1 = MapCollection::make([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $collection2 = MapCollection::make([
            'key2' => 'new_value2', // キーの重複（上書き）
            'key3' => 'value3',
        ]);

        $merged = $collection1->merge($collection2);

        $this->assertCount(3, $merged);
        $this->assertEquals('value1', $merged->get('key1'));
        $this->assertEquals('new_value2', $merged->get('key2')); // 上書きされた値
        $this->assertEquals('value3', $merged->get('key3'));
    }

    #[Test]
    public function mapメソッドで値を変換できる(): void
    {
        $collection = MapCollection::make([
            'key1' => 1,
            'key2' => 2,
        ]);

        $mapped = $collection->map(static fn ($value) => $value * 2);

        $this->assertEquals([
            'key1' => 2,
            'key2' => 4,
        ], $mapped->toArray());
    }

    #[Test]
    public function mapStrictメソッドで値を変換できる(): void
    {
        $collection = MapCollection::make([
            'key1' => 1,
            'key2' => 2,
        ]);

        $mapped = $collection->mapStrict(static fn ($value) => $value * 2);

        $this->assertEquals([
            'key1' => 2,
            'key2' => 4,
        ], $mapped->toArray());
        // mapStrictはmapと異なり、元のコレクションと同じ型を返す
        $this->assertInstanceOf($collection::class, $mapped);
    }

    #[Test]
    public function filterメソッドで条件に一致する要素だけを取得できる(): void
    {
        $collection = MapCollection::make([
            'key1' => 1,
            'key2' => 2,
            'key3' => 3,
            'key4' => 4,
        ]);

        $filtered = $collection->filter(static fn ($value) => $value % 2 === 0);

        $this->assertCount(2, $filtered);
        $this->assertEquals([
            'key2' => 2,
            'key4' => 4,
        ], $filtered->toArray());
    }

    #[Test]
    public function rejectメソッドで条件に一致しない要素だけを取得できる(): void
    {
        $collection = MapCollection::make([
            'key1' => 1,
            'key2' => 2,
            'key3' => 3,
            'key4' => 4,
        ]);

        $rejected = $collection->reject(static fn ($value) => $value % 2 === 0);

        $this->assertCount(2, $rejected);
        $this->assertEquals([
            'key1' => 1,
            'key3' => 3,
        ], $rejected->toArray());
    }

    #[Test]
    public function reduceメソッドで要素を集約できる(): void
    {
        $collection = MapCollection::make([
            'key1' => 1,
            'key2' => 2,
            'key3' => 3,
            'key4' => 4,
        ]);

        $sum = $collection->reduce(static fn ($carry, $value) => $carry + $value, 0);

        $this->assertEquals(10, $sum);
    }

    #[Test]
    public function hasメソッドでキーの存在確認ができる(): void
    {
        $collection = MapCollection::make([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $this->assertTrue($collection->has('key1'));
        $this->assertTrue($collection->has('key2'));
        $this->assertFalse($collection->has('key3'));
    }

    #[Test]
    public function sortメソッドで値に基づいてソートできる(): void
    {
        $collection = MapCollection::make([
            'key3' => 3,
            'key1' => 1,
            'key4' => 4,
            'key2' => 2,
        ]);

        $sorted = $collection->sort();

        // ソート後のキーと値の順序を確認
        $values = [];
        foreach ($sorted as $key => $value) {
            $values[] = $value;
        }

        $this->assertEquals([1, 2, 3, 4], $values);
    }

    #[Test]
    public function sortメソッドにクロージャを渡してカスタムソートができる(): void
    {
        $collection = MapCollection::make([
            'key3' => 3,
            'key1' => 1,
            'key4' => 4,
            'key2' => 2,
        ]);

        $sorted = $collection->sort(static fn ($a, $b) => $b <=> $a); // 降順

        // ソート後の値の順序を確認
        $values = [];
        foreach ($sorted as $key => $value) {
            $values[] = $value;
        }

        $this->assertEquals([4, 3, 2, 1], $values);
    }

    #[Test]
    public function valuesメソッドで値のListCollectionを取得できる(): void
    {
        $collection = MapCollection::make([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ]);

        $values = $collection->values();

        $this->assertInstanceOf(ListCollection::class, $values);
        $this->assertEquals(['value1', 'value2', 'value3'], $values->toArray());
    }

    #[Test]
    public function keysメソッドでキーのListCollectionを取得できる(): void
    {
        $collection = MapCollection::make([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ]);

        $keys = $collection->keys();

        $this->assertInstanceOf(ListCollection::class, $keys);
        $this->assertEquals(['key1', 'key2', 'key3'], $keys->toArray());
    }

    #[Test]
    public function removeメソッドでキーを削除できる(): void
    {
        $collection = MapCollection::make([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ]);

        $newCollection = $collection->remove('key2');

        $this->assertCount(2, $newCollection);
        $this->assertEquals([
            'key1' => 'value1',
            'key3' => 'value3',
        ], $newCollection->toArray());

        // 元のコレクションは変更されていないことを確認
        $this->assertCount(3, $collection);
    }

    #[Test]
    public function removeメソッドで存在しないキーを指定した場合は何も変わらない(): void
    {
        $collection = MapCollection::make([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $newCollection = $collection->remove('key3');

        $this->assertEquals($collection->toArray(), $newCollection->toArray());
    }

    #[Test]
    public function ArrayAccessインターフェースが実装されている(): void
    {
        $collection = MapCollection::make([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $this->assertTrue(isset($collection['key1']));
        $this->assertEquals('value1', $collection['key1']);
        $this->assertFalse(isset($collection['key3']));
    }

    #[Test]
    public function 不変性を維持するためoffsetSetで例外が発生する(): void
    {
        $collection = MapCollection::make([
            'key1' => 'value1',
        ]);

        $this->expectException(BadMethodCallException::class);
        $collection['key2'] = 'value2';
    }

    #[Test]
    public function 不変性を維持するためoffsetUnsetで例外が発生する(): void
    {
        $collection = MapCollection::make([
            'key1' => 'value1',
        ]);

        $this->expectException(BadMethodCallException::class);
        unset($collection['key1']);
    }

    #[Test]
    public function offsetGetで存在しないキーを指定すると例外が発生する(): void
    {
        $collection = MapCollection::make([
            'key1' => 'value1',
        ]);

        $this->expectException(OutOfBoundsException::class);
        $value = $collection['key2'];
    }

    #[Test]
    public function hasメソッドでキーの存在を確認できる(): void
    {
        // 文字列キーでのテスト
        $collection = MapCollection::make([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $this->assertTrue($collection->has('key1'));
        $this->assertTrue($collection->has('key2'));
        $this->assertFalse($collection->has('key3'));

    }
}
