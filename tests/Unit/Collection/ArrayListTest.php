<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Collection;

use BcMath\Number;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\Collection\ArrayList;
use WizDevelop\PhpValueObject\Collection\Exception\CollectionNotFoundException;
use WizDevelop\PhpValueObject\Collection\Exception\MultipleCollectionsFoundException;
use WizDevelop\PhpValueObject\Number\DecimalValue;
use WizDevelop\PhpValueObject\Number\IntegerValue;
use WizDevelop\PhpValueObject\String\StringValue;

#[TestDox('ArrayListクラスのテスト')]
#[CoversClass(ArrayList::class)]
final class ArrayListTest extends TestCase
{
    /**
     * @return array<string, array{array<mixed>}>
     */
    public static function 様々な要素のコレクションを提供(): array
    {
        return [
            'プリミティブ値の配列' => [[1, 2, 3, 4, 5]],
            '文字列の配列' => [['apple', 'banana', 'cherry']],
            '空の配列' => [[]],
            '混合型の配列' => [[1, 'string', true, 3.14]],
        ];
    }

    /**
     * @return array<string, array{array<mixed>}>
     */
    public static function 独自クラスを含むコレクションを提供(): array
    {
        return [
            'StringValue配列' => [[
                StringValue::from('apple'),
                StringValue::from('banana'),
                StringValue::from('cherry'),
            ]],
            'IntegerValue配列' => [[
                IntegerValue::from(10),
                IntegerValue::from(20),
                IntegerValue::from(30),
            ]],
            'DecimalValue配列' => [[
                DecimalValue::from(new Number('1.5')),
                DecimalValue::from(new Number('2.5')),
                DecimalValue::from(new Number('3.5')),
            ]],
            '混合ValueObject配列' => [[
                StringValue::from('test'),
                IntegerValue::from(10),
                DecimalValue::from(new Number('1.5')),
            ]],
        ];
    }

    /**
     * @param array<int,mixed> $elements
     */
    #[Test]
    #[DataProvider('様々な要素のコレクションを提供')]
    public function from静的メソッドでインスタンスが作成できる(array $elements): void
    {
        $collection = ArrayList::from($elements);

        $this->assertInstanceOf(ArrayList::class, $collection);
        $this->assertEquals($elements, $collection->toArray());
    }

    /**
     * @param array<int,mixed> $elements
     */
    #[Test]
    #[DataProvider('独自クラスを含むコレクションを提供')]
    public function 独自クラスのコレクションが作成できる(array $elements): void
    {
        $collection = ArrayList::from($elements);

        $this->assertInstanceOf(ArrayList::class, $collection);
        $this->assertEquals($elements, $collection->toArray());
    }

    #[Test]
    public function empty静的メソッドで空のコレクションが作成できる(): void
    {
        $collection = ArrayList::empty();

        $this->assertInstanceOf(ArrayList::class, $collection);
        $this->assertEmpty($collection->toArray());
        $this->assertEquals(0, $collection->count());
    }

    #[Test]
    public function make静的メソッドで様々なイテラブルからコレクションが作成できる(): void
    {
        // 配列から作成
        $array = [1, 2, 3];
        $collection1 = ArrayList::make($array);
        $this->assertEquals($array, $collection1->toArray());

        // Generatorから作成
        $generator = (static function () {
            yield 1;
            yield 2;
            yield 3;
        })();
        $collection2 = ArrayList::make($generator);
        $this->assertEquals([1, 2, 3], $collection2->toArray());

        // 別のArrayListから作成
        $original = ArrayList::from(['a', 'b', 'c']);
        $collection3 = ArrayList::make($original);
        $this->assertEquals(['a', 'b', 'c'], $collection3->toArray());
    }

    #[Test]
    public function first関数で先頭要素が取得できる(): void
    {
        $collection = ArrayList::from([10, 20, 30, 40]);

        $this->assertTrue($collection->first()->isSome());
        $this->assertEquals(10, $collection->first()->unwrap());

        // クロージャによるフィルタリング
        $this->assertTrue($collection->first(static fn ($value) => $value > 15)->isSome());
        $this->assertEquals(20, $collection->first(static fn ($value) => $value > 15)->unwrap());

        // 条件に合致する要素がない場合のデフォルト値
        $this->assertTrue($collection->first(static fn ($value) => $value > 100, 'default')->isSome());
        $this->assertEquals('default', $collection->first(static fn ($value) => $value > 100, 'default')->unwrap());

        // 空のコレクション
        $emptyCollection = ArrayList::empty();
        $this->assertTrue($emptyCollection->first()->isNone());
    }

    #[Test]
    public function firstOrFail関数で先頭要素が取得できる(): void
    {
        $collection = ArrayList::from([10, 20, 30]);

        $this->assertEquals(10, $collection->firstOrFail());
        $this->assertEquals(20, $collection->firstOrFail(static fn ($value) => $value > 15));
    }

    #[Test]
    public function firstOrFail関数で要素が見つからない場合は例外が発生する(): void
    {
        $this->expectException(CollectionNotFoundException::class);

        $emptyCollection = ArrayList::empty();
        $emptyCollection->firstOrFail();
    }

    #[Test]
    public function last関数で末尾要素が取得できる(): void
    {
        $collection = ArrayList::from([10, 20, 30, 40]);

        $this->assertTrue($collection->last()->isSome());
        $this->assertEquals(40, $collection->last()->unwrap());

        // クロージャによるフィルタリング
        $this->assertTrue($collection->last(static fn ($value) => $value < 35)->isSome());
        $this->assertEquals(30, $collection->last(static fn ($value) => $value < 35)->unwrap());

        // 条件に合致する要素がない場合のデフォルト値
        $this->assertTrue($collection->last(static fn ($value) => $value < 10, 'default')->isSome());
        $this->assertEquals('default', $collection->last(static fn ($value) => $value < 10, 'default')->unwrap());

        // 空のコレクション
        $emptyCollection = ArrayList::empty();
        $this->assertTrue($emptyCollection->last()->isNone());
    }

    #[Test]
    public function lastOrFail関数で末尾要素が取得できる(): void
    {
        $collection = ArrayList::from([10, 20, 30]);

        $this->assertEquals(30, $collection->lastOrFail());
        $this->assertEquals(20, $collection->lastOrFail(static fn ($value) => $value < 25));
    }

    #[Test]
    public function lastOrFail関数で要素が見つからない場合は例外が発生する(): void
    {
        $this->expectException(CollectionNotFoundException::class);

        $emptyCollection = ArrayList::empty();
        $emptyCollection->lastOrFail();
    }

    #[Test]
    public function sole関数で条件に合う要素が1つだけ存在する場合にその要素が取得できる(): void
    {
        $collection = ArrayList::from([10, 20, 30]);

        $this->assertEquals(20, $collection->sole(static fn ($value) => $value === 20));
    }

    #[Test]
    public function sole関数で条件に合う要素がない場合は例外が発生する(): void
    {
        $this->expectException(CollectionNotFoundException::class);

        $collection = ArrayList::from([10, 20, 30]);
        $collection->sole(static fn ($value) => $value > 100);
    }

    #[Test]
    public function sole関数で条件に合う要素が複数ある場合は例外が発生する(): void
    {
        $this->expectException(MultipleCollectionsFoundException::class);

        $collection = ArrayList::from([10, 20, 30]);
        $collection->sole(static fn ($value) => $value > 15);
    }

    #[Test]
    public function slice関数で部分コレクションが取得できる(): void
    {
        $collection = ArrayList::from([10, 20, 30, 40, 50]);

        // 先頭から3要素
        $slice1 = $collection->slice(0, 3);
        $this->assertEquals([10, 20, 30], $slice1->toArray());

        // インデックス2から2要素
        $slice2 = $collection->slice(2, 2);
        $this->assertEquals([30, 40], $slice2->toArray());

        // 末尾要素
        $slice3 = $collection->slice(4);
        $this->assertEquals([50], $slice3->toArray());

        // 範囲外のスライス
        $slice4 = $collection->slice(5);
        $this->assertEquals([], $slice4->toArray());
    }

    #[Test]
    public function reverse関数で要素が逆順になったコレクションが取得できる(): void
    {
        $collection = ArrayList::from([10, 20, 30]);
        $reversed = $collection->reverse();

        $this->assertEquals([30, 20, 10], $reversed->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals([10, 20, 30], $collection->toArray());
    }

    #[Test]
    public function 配列アクセスができる(): void
    {
        $collection = ArrayList::from(['a', 'b', 'c']);

        $this->assertEquals('a', $collection[0]);
        $this->assertEquals('b', $collection[1]);
        $this->assertEquals('c', $collection[2]);

        $this->assertTrue(isset($collection[0]));
        $this->assertFalse(isset($collection[3]));
    }

    #[Test]
    public function push関数で要素を追加したコレクションが取得できる(): void
    {
        $collection = ArrayList::from([1, 2, 3]);

        $newCollection = $collection->push(4, 5);
        $this->assertEquals([1, 2, 3, 4, 5], $newCollection->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals([1, 2, 3], $collection->toArray());
    }

    #[Test]
    public function add関数で要素を追加したコレクションが取得できる(): void
    {
        $collection = ArrayList::from([1, 2, 3]);

        $newCollection = $collection->add(4);
        $this->assertEquals([1, 2, 3, 4], $newCollection->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals([1, 2, 3], $collection->toArray());
    }

    #[Test]
    public function concat関数で別のコレクションと連結したコレクションが取得できる(): void
    {
        $collection1 = ArrayList::from([1, 2, 3]);
        $collection2 = ArrayList::from([4, 5, 6]);

        $concatCollection = $collection1->concat($collection2);
        $this->assertEquals([1, 2, 3, 4, 5, 6], $concatCollection->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals([1, 2, 3], $collection1->toArray());
        $this->assertEquals([4, 5, 6], $collection2->toArray());
    }

    #[Test]
    public function merge関数で別のコレクションと結合したコレクションが取得できる(): void
    {
        $collection1 = ArrayList::from([1, 2, 3]);
        $collection2 = ArrayList::from([4, 5, 6]);

        $mergedCollection = $collection1->merge($collection2);
        $this->assertEquals([1, 2, 3, 4, 5, 6], $mergedCollection->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals([1, 2, 3], $collection1->toArray());
        $this->assertEquals([4, 5, 6], $collection2->toArray());
    }

    #[Test]
    public function map関数で各要素を変換したコレクションが取得できる(): void
    {
        $collection = ArrayList::from([1, 2, 3]);

        $mappedCollection = $collection->map(static fn ($value) => $value * 2);
        $this->assertEquals([2, 4, 6], $mappedCollection->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals([1, 2, 3], $collection->toArray());
    }

    #[Test]
    public function mapStrict関数で型情報を保持したまま各要素を変換できる(): void
    {
        $collection = ArrayList::from([1, 2, 3]);

        $mappedCollection = $collection->mapStrict(static fn ($value) => $value * 2);
        $this->assertEquals([2, 4, 6], $mappedCollection->toArray());

        // 同じ型のインスタンスである
        $this->assertInstanceOf(ArrayList::class, $mappedCollection);
    }

    #[Test]
    public function filter関数で条件に合う要素のみのコレクションが取得できる(): void
    {
        $collection = ArrayList::from([1, 2, 3, 4, 5]);

        $filteredCollection = $collection->filter(static fn ($value) => $value % 2 === 0);
        $this->assertEquals([1 => 2, 3 => 4], $filteredCollection->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals([1, 2, 3, 4, 5], $collection->toArray());
    }

    #[Test]
    public function reject関数で条件に合わない要素のみのコレクションが取得できる(): void
    {
        $collection = ArrayList::from([1, 2, 3, 4, 5]);

        $rejectedCollection = $collection->reject(static fn ($value) => $value % 2 === 0);
        $this->assertEquals([0 => 1, 2 => 3, 4 => 5], $rejectedCollection->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals([1, 2, 3, 4, 5], $collection->toArray());
    }

    #[Test]
    public function reduce関数で要素を集約できる(): void
    {
        $collection = ArrayList::from([1, 2, 3, 4, 5]);

        $sum = $collection->reduce(static fn ($carry, $value) => $carry + $value, 0);
        $this->assertEquals(15, $sum);

        $product = $collection->reduce(static fn ($carry, $value) => $carry * $value, 1);
        $this->assertEquals(120, $product);

        $concatenated = ArrayList::from(['a', 'b', 'c'])
            ->reduce(static fn ($carry, $value) => $carry . $value, '');
        $this->assertEquals('abc', $concatenated);
    }

    #[Test]
    public function unique関数で重複のない要素のコレクションが取得できる(): void
    {
        $collection = ArrayList::from([1, 2, 2, 3, 3, 3, 4]);

        $uniqueCollection = $collection->unique();
        $this->assertEquals([0 => 1, 1 => 2, 3 => 3, 6 => 4], $uniqueCollection->toArray());

        // カスタムな一意性の基準
        $users = ArrayList::from([
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
            ['id' => 1, 'name' => 'Alice (duplicate)'],
            ['id' => 3, 'name' => 'Charlie'],
        ]);

        $uniqueUsers = $users->unique(static fn ($user) => $user['id']);
        $this->assertCount(3, $uniqueUsers);
    }

    #[Test]
    public function contains関数で特定の要素が含まれているか確認できる(): void
    {
        $collection = ArrayList::from([1, 2, 3, 4, 5]);

        $this->assertTrue($collection->contains(3));
        $this->assertFalse($collection->contains(6));

        // クロージャを使った検索
        $this->assertTrue($collection->contains(static fn ($value) => $value > 4));
        $this->assertFalse($collection->contains(static fn ($value) => $value > 5));
    }

    #[Test]
    public function every関数ですべての要素が条件を満たすか確認できる(): void
    {
        $collection = ArrayList::from([2, 4, 6, 8]);

        $this->assertTrue($collection->every(static fn ($value) => $value % 2 === 0));
        $this->assertFalse($collection->every(static fn ($value) => $value > 5));

        // 値の比較
        $allTwos = ArrayList::from([2, 2, 2, 2]);
        $this->assertTrue($allTwos->every(2));
        $this->assertFalse($collection->every(2));
    }

    #[Test]
    public function sort関数で要素をソートしたコレクションが取得できる(): void
    {
        $collection = ArrayList::from([3, 1, 4, 2, 5]);

        // デフォルトソート（昇順）
        $sortedCollection = $collection->sort();
        $this->assertEquals([1, 2, 3, 4, 5], array_values($sortedCollection->toArray()));

        // カスタムソート（降順）
        $customSortedCollection = $collection->sort(static fn ($a, $b) => $b <=> $a);
        $this->assertEquals([5, 4, 3, 2, 1], array_values($customSortedCollection->toArray()));

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals([3, 1, 4, 2, 5], $collection->toArray());
    }
}
