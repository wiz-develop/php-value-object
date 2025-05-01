<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Collection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\Collection\Pair;
use WizDevelop\PhpValueObject\Examples\Collection\LimitedMap;

#[TestDox('LimitedMapクラスのテスト')]
#[CoversClass(LimitedMap::class)]
final class LimitedMapTest extends TestCase
{
    #[Test]
    public function tryFrom静的メソッドで有効なPair配列から成功結果が取得できる(): void
    {
        // 有効な範囲（2〜5要素）
        $validPairs = [
            Pair::of('key1', 'value1'),
            Pair::of('key2', 'value2'),
            Pair::of('key3', 'value3'),
        ];

        $result = LimitedMap::tryFrom(...$validPairs);

        $this->assertTrue($result->isOk());
        $map = $result->unwrap();
        $this->assertInstanceOf(LimitedMap::class, $map);

        $expected = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $this->assertEquals($expected, $map->toArray());
    }

    #[Test]
    public function tryFrom静的メソッドで最小要素数を下回るPair配列からエラー結果が取得できる(): void
    {
        // 最小要素数違反（1要素しかない）
        $tooFewPairs = [
            Pair::of('key1', 'value1'),
        ];

        $result = LimitedMap::tryFrom(...$tooFewPairs);

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function tryFrom静的メソッドで最大要素数を超えるPair配列からエラー結果が取得できる(): void
    {
        // 最大要素数違反（6要素ある）
        $tooManyPairs = [
            Pair::of('key1', 'value1'),
            Pair::of('key2', 'value2'),
            Pair::of('key3', 'value3'),
            Pair::of('key4', 'value4'),
            Pair::of('key5', 'value5'),
            Pair::of('key6', 'value6'),
        ];

        $result = LimitedMap::tryFrom(...$tooManyPairs);

        $this->assertTrue($result->isErr());
    }
}
