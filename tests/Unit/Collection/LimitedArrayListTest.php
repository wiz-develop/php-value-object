<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Collection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\Examples\Collection\LimitedArrayList;

#[TestDox('LimitedArrayListクラスのテスト')]
#[CoversClass(LimitedArrayList::class)]
final class LimitedArrayListTest extends TestCase
{
    #[Test]
    public function tryFrom静的メソッドで有効な配列から成功結果が取得できる(): void
    {
        // 有効な範囲（2〜5要素）
        $validElements = [1, 2, 3];
        $result = LimitedArrayList::tryFrom($validElements);

        $this->assertTrue($result->isOk());
        $collection = $result->unwrap();
        $this->assertInstanceOf(LimitedArrayList::class, $collection);
        $this->assertEquals($validElements, $collection->toArray());
    }

    #[Test]
    public function tryFrom静的メソッドで最小要素数を下回る配列からエラー結果が取得できる(): void
    {
        // 最小要素数違反（1要素しかない）
        $tooFewElements = [1];
        $result = LimitedArrayList::tryFrom($tooFewElements);

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function tryFrom静的メソッドで最大要素数を超える配列からエラー結果が取得できる(): void
    {
        // 最大要素数違反（6要素ある）
        $tooManyElements = [1, 2, 3, 4, 5, 6];
        $result = LimitedArrayList::tryFrom($tooManyElements);

        $this->assertTrue($result->isErr());
    }
}
