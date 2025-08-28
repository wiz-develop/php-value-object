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
    #[DataProvider('provide独自クラスのコレクションが作成できるCases')]
    public function 独自クラスのコレクションが作成できる(array $elements): void
    {
        $collection = ArrayList::from($elements);

        $this->assertInstanceOf(ArrayList::class, $collection);
        $this->assertEquals($elements, $collection->toArray());
    }

    /**
     * @return array<string, array{array<mixed>}>
     */
    public static function provide独自クラスのコレクションが作成できるCases(): iterable
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

    /**
     * @param array<int,mixed> $elements
     */
    #[Test]
    #[DataProvider('様々な要素のコレクションを提供')]
    public function tryFrom静的メソッドで有効な配列から成功結果が取得できる(array $elements): void
    {
        $result = ArrayList::tryFrom($elements);

        $this->assertTrue($result->isOk());
        $collection = $result->unwrap();
        $this->assertInstanceOf(ArrayList::class, $collection);
        $this->assertEquals($elements, $collection->toArray());
    }

    /**
     * @return array<string, array{array<mixed>}>
     */
    public static function 様々な要素のコレクションを提供(): iterable
    {
        return [
            'プリミティブ値の配列' => [[1, 2, 3, 4, 5]],
            '文字列の配列' => [['apple', 'banana', 'cherry']],
            '空の配列' => [[]],
            '混合型の配列' => [[1, 'string', true, 3.14]],
        ];
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

    #[Test]
    public function filter関数でselfを返すことができる(): void
    {
        $collection = ArrayList::from([1, 2, 3, 4, 5]);

        $filtered = $collection->filter(static fn ($value) => $value % 2 === 0);

        // 戻り値がArrayListインスタンス（self）であることを確認
        $this->assertInstanceOf(ArrayList::class, $filtered);
        $this->assertEquals([1 => 2, 3 => 4], $filtered->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals([1, 2, 3, 4, 5], $collection->toArray());
    }

    #[Test]
    public function filterStrict関数でstaticを返すことができる(): void
    {
        $collection = ArrayList::from([1, 2, 3, 4, 5]);

        $filtered = $collection->filterStrict(static fn ($value) => $value % 2 === 0);

        // 戻り値が正確な型（static）であることを確認
        $this->assertInstanceOf(ArrayList::class, $filtered);
        $this->assertEquals([1 => 2, 3 => 4], $filtered->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals([1, 2, 3, 4, 5], $collection->toArray());
    }

    #[Test]
    public function flatMap関数で各要素を変換して平坦化できる(): void
    {
        // 基本的な変換（各数値を2倍にして配列に包む）
        $collection = ArrayList::from([1, 2, 3]);
        $mapped = $collection->flatMap(static fn ($value) => [$value * 2]);

        $this->assertInstanceOf(ArrayList::class, $mapped);
        $this->assertEquals([2, 4, 6], $mapped->toArray());

        // 各要素を複数の要素に展開
        $collection2 = ArrayList::from([1, 2, 3]);
        $expanded = $collection2->flatMap(static fn ($value) => [$value, $value * 10]);

        $this->assertEquals([1, 10, 2, 20, 3, 30], $expanded->toArray());

        // 空の配列を返す場合
        $collection3 = ArrayList::from([1, 2, 3]);
        $filtered = $collection3->flatMap(static fn ($value) => $value % 2 === 0 ? [$value] : []);

        $this->assertEquals([2], $filtered->toArray());

        // 2次元配列の平坦化（従来のflattenと同等の動作）
        $collection4 = ArrayList::from([[1, 2], [3, 4], [5, 6]]);
        $flattened = $collection4->flatMap(static fn ($array) => $array);

        $this->assertEquals([1, 2, 3, 4, 5, 6], $flattened->toArray());
        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals([1, 2, 3], $collection->toArray());

        // objectの2次元配列の平坦化（従来のflattenと同等の動作）
        $collection5 = ArrayList::from([
            ArrayList::from([1, 2]),
            ArrayList::from([3, 4]),
            ArrayList::from([5, 6]),
        ]);
        $flattenedObjects = $collection5->flatMap(static fn ($array) => $array);
        $this->assertEquals([1, 2, 3, 4, 5, 6], $flattenedObjects->toArray());
        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals([ArrayList::from([1, 2]), ArrayList::from([3, 4]), ArrayList::from([5, 6])], $collection5->toArray());
    }

    #[Test]
    public function flatMap関数で空配列を含む場合も正しく平坦化できる(): void
    {
        $collection = ArrayList::from([1, 2, 3]);

        // 空配列を返す場合
        $flatMapped = $collection->flatMap(static fn ($value) => $value % 2 === 0 ? [] : [$value * 2]);

        $this->assertInstanceOf(ArrayList::class, $flatMapped);
        $this->assertEquals([2, 6], $flatMapped->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals([1, 2, 3], $collection->toArray());
    }

    #[Test]
    public function flatten関数でネストされたコレクションを平坦化できる(): void
    {
        $collection = ArrayList::from([[1, 2], [3, 4, [5, 6, [7]]]]);

        $flattened = $collection->flatten();

        $this->assertInstanceOf(ArrayList::class, $flattened);
        $this->assertEquals([1, 2, 3, 4, 5, 6, 7], $flattened->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals([[1, 2], [3, 4, [5, 6, [7]]]], $collection->toArray());
    }

    #[Test]
    public function flatten関数で空のコレクションも正しく平坦化できる(): void
    {
        $collection = ArrayList::from([[], [1, 2], [], [3, 4]]);

        $flattened = $collection->flatten();

        $this->assertInstanceOf(ArrayList::class, $flattened);
        $this->assertEquals([1, 2, 3, 4], $flattened->toArray());

        // 元のコレクションは変更されない（イミュータブル）
        $this->assertEquals([[], [1, 2], [], [3, 4]], $collection->toArray());
    }

    #[Test]
    public function filterAs関数で特定のクラスのインスタンスのみを含むコレクションが取得できる(): void
    {
        $collection = ArrayList::from([
            StringValue::from('apple'),
            IntegerValue::from(10),
            StringValue::from('banana'),
            DecimalValue::from(new Number('2.5')),
        ]);

        $filtered = $collection
            ->filterAs(StringValue::class)
            ->values();

        // @phpstan-ignore-next-line
        $this->assertContainsOnlyInstancesOf(StringValue::class, $filtered);

        $this->assertCount(2, $filtered);
        $this->assertEquals('apple', $filtered[0]->value);
        $this->assertEquals('banana', $filtered[1]->value);
    }

    #[Test]
    public function values関数でキーが連続した整数にリセットされた新しいコレクションが取得できる(): void
    {
        $collection = ArrayList::from([10, 20, 30]);

        $filteredCollection = $collection->filter(static fn ($x) => $x >= 20);
        $this->assertCount(2, $filteredCollection);
        $this->assertEquals($filteredCollection[1], 20);
        $this->assertEquals($filteredCollection[2], 30);

        $valuesCollection = $filteredCollection->values();
        $this->assertCount(2, $valuesCollection);
        $this->assertEquals($valuesCollection[0], 20);
        $this->assertEquals($valuesCollection[1], 30);
    }

    #[Test]
    public function mapToDictionary関数で要素をキーごとにグループ化できる(): void
    {
        // 基本的なケース：文字列の長さでグループ化
        $collection = ArrayList::from(['one', 'two', 'three', 'four', 'five']);
        $dictionary = $collection->mapToDictionary(static fn ($value) => [mb_strlen($value) => $value]);

        $this->assertArrayHasKey(3, $dictionary);
        $this->assertArrayHasKey(4, $dictionary);
        $this->assertArrayHasKey(5, $dictionary);
        $this->assertEquals(['one', 'two'], $dictionary[3]);
        $this->assertEquals(['four', 'five'], $dictionary[4]);
        $this->assertEquals(['three'], $dictionary[5]);

        // 数値の偶数・奇数でグループ化
        $numbers = ArrayList::from([1, 2, 3, 4, 5, 6]);
        $evenOddDict = $numbers->mapToDictionary(static fn ($value) => [$value % 2 === 0 ? 'even' : 'odd' => $value]);

        $this->assertArrayHasKey('odd', $evenOddDict);
        $this->assertArrayHasKey('even', $evenOddDict);
        $this->assertEquals([1, 3, 5], $evenOddDict['odd']);
        $this->assertEquals([2, 4, 6], $evenOddDict['even']);

        // インデックスを使用した場合
        $indexed = ArrayList::from(['a', 'b', 'c']);
        $indexedDict = $indexed->mapToDictionary(static fn ($value, $index) => [$index % 2 => $value]);

        $this->assertEquals(['a', 'c'], $indexedDict[0]);
        $this->assertEquals(['b'], $indexedDict[1]);
    }

    #[Test]
    public function mapToDictionary関数で空のコレクションを処理できる(): void
    {
        $empty = ArrayList::empty();
        $dictionary = $empty->mapToDictionary(static fn ($value) => ['key' => $value]);

        $this->assertEmpty($dictionary);
    }

    #[Test]
    public function mapToDictionary関数でValueObjectを使用できる(): void
    {
        $collection = ArrayList::from([
            StringValue::from('apple'),
            StringValue::from('banana'),
            StringValue::from('avocado'),
            StringValue::from('blueberry'),
        ]);

        // 最初の文字でグループ化
        $dictionary = $collection->mapToDictionary(
            static fn (StringValue $value) => [mb_substr($value->value, 0, 1) => $value]
        );

        $this->assertCount(2, $dictionary);
        $this->assertArrayHasKey('a', $dictionary);
        $this->assertArrayHasKey('b', $dictionary);

        $this->assertCount(2, $dictionary['a']);
        $this->assertEquals('apple', $dictionary['a'][0]->value);
        $this->assertEquals('avocado', $dictionary['a'][1]->value);

        $this->assertCount(2, $dictionary['b']);
        $this->assertEquals('banana', $dictionary['b'][0]->value);
        $this->assertEquals('blueberry', $dictionary['b'][1]->value);
    }

    #[Test]
    public function mapToGroups関数で要素をキーごとにArrayListのグループにできる(): void
    {
        // 基本的なケース：文字列の長さでグループ化
        $collection = ArrayList::from(['one', 'two', 'three', 'four', 'five']);
        $groups = $collection->mapToGroups(static fn ($value) => [mb_strlen($value) => $value]);

        $this->assertArrayHasKey(3, $groups);
        $this->assertArrayHasKey(4, $groups);
        $this->assertArrayHasKey(5, $groups);

        // 各グループがArrayListインスタンスであることを確認
        $this->assertInstanceOf(ArrayList::class, $groups[3]);
        $this->assertInstanceOf(ArrayList::class, $groups[4]);
        $this->assertInstanceOf(ArrayList::class, $groups[5]);

        // グループの内容を確認
        $this->assertEquals(['one', 'two'], $groups[3]->toArray());
        $this->assertEquals(['four', 'five'], $groups[4]->toArray());
        $this->assertEquals(['three'], $groups[5]->toArray());

        // 数値の偶数・奇数でグループ化
        $numbers = ArrayList::from([1, 2, 3, 4, 5, 6]);
        $evenOddGroups = $numbers->mapToGroups(static fn ($value) => [$value % 2 === 0 ? 'even' : 'odd' => $value]);

        $this->assertInstanceOf(ArrayList::class, $evenOddGroups['odd']);
        $this->assertInstanceOf(ArrayList::class, $evenOddGroups['even']);
        $this->assertEquals([1, 3, 5], $evenOddGroups['odd']->toArray());
        $this->assertEquals([2, 4, 6], $evenOddGroups['even']->toArray());
    }

    #[Test]
    public function mapToGroups関数で空のコレクションを処理できる(): void
    {
        $empty = ArrayList::empty();
        $groups = $empty->mapToGroups(static fn ($value) => ['key' => $value]);

        $this->assertEmpty($groups);
    }

    #[Test]
    public function mapToGroups関数でインデックスを使用できる(): void
    {
        $collection = ArrayList::from(['a', 'b', 'c', 'd']);
        $groups = $collection->mapToGroups(static fn ($value, $index) => [$index % 2 === 0 ? 'even_index' : 'odd_index' => $value]);

        $this->assertInstanceOf(ArrayList::class, $groups['even_index']);
        $this->assertInstanceOf(ArrayList::class, $groups['odd_index']);

        $this->assertEquals(['a', 'c'], $groups['even_index']->toArray());
        $this->assertEquals(['b', 'd'], $groups['odd_index']->toArray());
    }

    #[Test]
    public function mapToGroups関数で作成されたグループはArrayListの全機能を使用できる(): void
    {
        $collection = ArrayList::from([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $groups = $collection->mapToGroups(static fn ($value) => [$value % 3 => $value]);

        // 余り0のグループ
        $this->assertInstanceOf(ArrayList::class, $groups[0]);
        $remainder0 = $groups[0];
        $this->assertEquals([3, 6, 9], $remainder0->toArray());

        // グループに対してArrayListのメソッドが使用できる
        $doubled = $remainder0->map(static fn ($v) => $v * 2);
        $this->assertEquals([6, 12, 18], $doubled->toArray());

        $sum = $remainder0->reduce(static fn ($carry, $v) => $carry + $v, 0);
        $this->assertEquals(18, $sum);

        // 余り1のグループ
        $remainder1 = $groups[1];
        $filtered = $remainder1->filter(static fn ($v) => $v > 5);
        $this->assertEquals([7, 10], $filtered->values()->toArray());
    }
}
