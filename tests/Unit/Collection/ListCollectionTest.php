<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Collection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\Collection\Exception\CollectionNotFoundException;
use WizDevelop\PhpValueObject\Collection\Exception\MultipleCollectionsFoundException;
use WizDevelop\PhpValueObject\Collection\ListCollection;

#[TestDox('ListCollectionクラスのテスト')]
#[CoversClass(ListCollection::class)]
final class ListCollectionTest extends TestCase
{
    #[Test]
    public function 空のコレクションが作成できる(): void
    {
        $collection = ListCollection::empty();

        $this->assertInstanceOf(ListCollection::class, $collection);
        $this->assertCount(0, $collection);
        $this->assertEquals([], $collection->toArray());
    }

    #[Test]
    public function from静的メソッドでコレクションが作成できる(): void
    {
        $elements = [1, 2, 3];
        $collection = ListCollection::from($elements);

        $this->assertInstanceOf(ListCollection::class, $collection);
        $this->assertCount(3, $collection);
        $this->assertEquals($elements, $collection->toArray());
    }

    #[Test]
    public function tryFrom静的メソッドでコレクションが作成できる(): void
    {
        $elements = [1, 2, 3];
        $result = ListCollection::tryFrom($elements);

        $this->assertTrue($result->isOk());
        $collection = $result->unwrap();
        $this->assertInstanceOf(ListCollection::class, $collection);
        $this->assertEquals($elements, $collection->toArray());
    }

    #[Test]
    public function make静的メソッドでコレクションが作成できる(): void
    {
        $elements = [1, 2, 3];
        $collection = ListCollection::make($elements);

        $this->assertInstanceOf(ListCollection::class, $collection);
        $this->assertCount(3, $collection);
        $this->assertEquals($elements, $collection->toArray());
    }

    #[Test]
    public function iteratorが正しく値を返す(): void
    {
        $elements = [1, 2, 3];
        $collection = ListCollection::from($elements);

        $values = [];
        foreach ($collection as $value) {
            $values[] = $value;
        }

        $this->assertEquals($elements, $values);
    }

    #[Test]
    public function firstメソッドで最初の要素を取得できる(): void
    {
        $elements = [1, 2, 3];
        $collection = ListCollection::from($elements);

        $this->assertEquals(1, $collection->first());
    }

    #[Test]
    public function firstメソッドにクロージャを渡して条件に一致する最初の要素を取得できる(): void
    {
        $elements = [1, 2, 3, 4, 5];
        $collection = ListCollection::from($elements);

        $result = $collection->first(static fn ($value) => $value > 3);

        $this->assertEquals(4, $result);
    }

    #[Test]
    public function firstメソッドで条件に一致する要素がない場合はデフォルト値を返す(): void
    {
        $elements = [1, 2, 3];
        $collection = ListCollection::from($elements);

        $result = $collection->first(static fn ($value) => $value > 10, 'default');

        $this->assertEquals('default', $result);
    }

    #[Test]
    public function firstOrFailメソッドで最初の要素を取得できる(): void
    {
        $elements = [1, 2, 3];
        $collection = ListCollection::from($elements);

        $this->assertEquals(1, $collection->firstOrFail());
    }

    #[Test]
    public function firstOrFailメソッドで要素がない場合は例外が発生する(): void
    {
        $collection = ListCollection::empty();

        $this->expectException(CollectionNotFoundException::class);
        $collection->firstOrFail();
    }

    #[Test]
    public function lastメソッドで最後の要素を取得できる(): void
    {
        $elements = [1, 2, 3];
        $collection = ListCollection::from($elements);

        $this->assertEquals(3, $collection->last());
    }

    #[Test]
    public function lastメソッドにクロージャを渡して条件に一致する最後の要素を取得できる(): void
    {
        $elements = [1, 2, 3, 4, 5];
        $collection = ListCollection::from($elements);

        $result = $collection->last(static fn ($value) => $value < 4);

        $this->assertEquals(3, $result);
    }

    #[Test]
    public function lastOrFailメソッドで最後の要素を取得できる(): void
    {
        $elements = [1, 2, 3];
        $collection = ListCollection::from($elements);

        $this->assertEquals(3, $collection->lastOrFail());
    }

    #[Test]
    public function lastOrFailメソッドで要素がない場合は例外が発生する(): void
    {
        $collection = ListCollection::empty();

        $this->expectException(CollectionNotFoundException::class);
        $collection->lastOrFail();
    }

    #[Test]
    public function reverseメソッドでコレクションを逆順にできる(): void
    {
        $elements = [1, 2, 3];
        $collection = ListCollection::from($elements);

        $reversed = $collection->reverse();

        $this->assertEquals([3, 2, 1], $reversed->toArray());
    }

    #[Test]
    public function soleメソッドで唯一の要素を取得できる(): void
    {
        $collection = ListCollection::from([42]);

        $this->assertEquals(42, $collection->sole());
    }

    #[Test]
    public function soleメソッドで要素がない場合は例外が発生する(): void
    {
        $collection = ListCollection::empty();

        $this->expectException(CollectionNotFoundException::class);
        $collection->sole();
    }

    #[Test]
    public function soleメソッドで複数の要素がある場合は例外が発生する(): void
    {
        $collection = ListCollection::from([1, 2]);

        $this->expectException(MultipleCollectionsFoundException::class);
        $collection->sole();
    }

    #[Test]
    public function soleメソッドにクロージャを渡して条件に一致する唯一の要素を取得できる(): void
    {
        $collection = ListCollection::from([1, 2, 3, 4, 5]);

        $result = $collection->sole(static fn ($value) => $value === 3);

        $this->assertEquals(3, $result);
    }

    #[Test]
    public function sliceメソッドでコレクションの一部を取得できる(): void
    {
        $elements = [1, 2, 3, 4, 5];
        $collection = ListCollection::from($elements);

        $sliced = $collection->slice(1, 3);

        $this->assertEquals([2, 3, 4], $sliced->toArray());
    }

    #[Test]
    public function pushメソッドで要素を追加できる(): void
    {
        $collection = ListCollection::from([1, 2]);

        $newCollection = $collection->push(3, 4);

        $this->assertEquals([1, 2, 3, 4], $newCollection->toArray());
        // 元のコレクションは変更されていないことを確認
        $this->assertEquals([1, 2], $collection->toArray());
    }

    #[Test]
    public function concatメソッドで別のコレクションと連結できる(): void
    {
        $collection1 = ListCollection::from([1, 2]);
        $collection2 = ListCollection::from([3, 4]);

        $concatenated = $collection1->concat($collection2);

        $this->assertEquals([1, 2, 3, 4], $concatenated->toArray());
    }

    #[Test]
    public function mergeメソッドで別のコレクションとマージできる(): void
    {
        $collection1 = ListCollection::from([1, 2]);
        $collection2 = ListCollection::from([3, 4]);

        $merged = $collection1->merge($collection2);

        $this->assertEquals([1, 2, 3, 4], $merged->toArray());
    }

    #[Test]
    public function mapメソッドで要素を変換できる(): void
    {
        $collection = ListCollection::from([1, 2, 3]);

        $mapped = $collection->map(static fn ($value) => $value * 2);

        $this->assertEquals([2, 4, 6], $mapped->toArray());
    }

    #[Test]
    public function mapStrictメソッドで要素を変換できる(): void
    {
        $collection = ListCollection::from([1, 2, 3]);

        $mapped = $collection->mapStrict(static fn ($value) => $value * 2);

        $this->assertEquals([2, 4, 6], $mapped->toArray());
        // mapStrictはmapと異なり、元のコレクションと同じ型を返す
        $this->assertInstanceOf($collection::class, $mapped);
    }

    #[Test]
    public function filterメソッドで条件に一致する要素だけを取得できる(): void
    {
        $collection = ListCollection::from([1, 2, 3, 4, 5]);

        $filtered = $collection->filter(static fn ($value) => $value % 2 === 0);

        $this->assertEquals([1 => 2, 3 => 4], $filtered->toArray());
    }

    #[Test]
    public function rejectメソッドで条件に一致しない要素だけを取得できる(): void
    {
        $collection = ListCollection::from([1, 2, 3, 4, 5]);

        $rejected = $collection->reject(static fn ($value) => $value % 2 === 0);

        $this->assertEquals([0 => 1, 2 => 3, 4 => 5], $rejected->toArray());
    }

    #[Test]
    public function uniqueメソッドで重複のない要素を取得できる(): void
    {
        $collection = ListCollection::from([1, 2, 2, 3, 3, 3]);

        $unique = $collection->unique();

        $this->assertEquals([0 => 1, 1 => 2, 3 => 3], $unique->toArray());
    }

    #[Test]
    public function filterメソッドで数値の条件でフィルタリングできる(): void
    {
        $collection = ListCollection::from([1, 2, 3, 4, 5]);

        $filtered = $collection->filter(static function ($value) {
            return $value % 2 === 0; // 偶数のみをフィルタリング
        });

        $this->assertCount(2, $filtered);
        $this->assertEquals([1 => 2, 3 => 4], $filtered->toArray());
    }

    #[Test]
    public function reduceメソッドで要素を集約できる(): void
    {
        $collection = ListCollection::from([1, 2, 3, 4]);

        $sum = $collection->reduce(static fn ($carry, $value) => $carry + $value, 0);

        $this->assertEquals(10, $sum);
    }

    #[Test]
    public function containsメソッドで要素の存在確認ができる(): void
    {
        $collection = ListCollection::from([1, 2, 3]);

        $this->assertTrue($collection->contains(2));
        $this->assertFalse($collection->contains(4));
    }

    #[Test]
    public function containsメソッドにクロージャを渡して条件に一致する要素の存在確認ができる(): void
    {
        $collection = ListCollection::from([1, 2, 3, 4, 5]);

        $this->assertTrue($collection->contains(static fn ($value) => $value > 3));
        $this->assertFalse($collection->contains(static fn ($value) => $value > 10));
    }

    #[Test]
    public function everyメソッドですべての要素が条件を満たすか確認できる(): void
    {
        $collection1 = ListCollection::from([2, 4, 6, 8]);
        $collection2 = ListCollection::from([2, 3, 4, 5]);

        $this->assertTrue($collection1->every(static fn ($value) => $value % 2 === 0));
        $this->assertFalse($collection2->every(static fn ($value) => $value % 2 === 0));
    }

    #[Test]
    public function addメソッドで要素を追加できる(): void
    {
        $collection = ListCollection::from([1, 2]);

        $newCollection = $collection->add(3);

        $this->assertEquals([1, 2, 3], $newCollection->toArray());
        // 元のコレクションは変更されていないことを確認
        $this->assertEquals([1, 2], $collection->toArray());
    }

    #[Test]
    public function sortメソッドで要素をソートできる(): void
    {
        $collection = ListCollection::from([3, 1, 4, 2]);

        $sorted = $collection->sort();

        $this->assertEquals([1, 2, 3, 4], array_values($sorted->toArray()));
    }

    #[Test]
    public function sortメソッドにクロージャを渡してカスタムソートができる(): void
    {
        $collection = ListCollection::from([3, 1, 4, 2]);

        $sorted = $collection->sort(static fn ($a, $b) => $b <=> $a); // 降順

        $this->assertEquals([4, 3, 2, 1], array_values($sorted->toArray()));
    }

    #[Test]
    public function ArrayAccessインターフェースが実装されている(): void
    {
        $collection = ListCollection::from(['a', 'b', 'c']);

        $this->assertEquals('a', $collection[0]);
        $this->assertEquals('b', $collection[1]);
        $this->assertEquals('c', $collection[2]);

        $this->assertTrue(isset($collection[0]));
        $this->assertFalse(isset($collection[3]));
    }
}
