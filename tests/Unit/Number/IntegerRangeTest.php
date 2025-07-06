<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Number;

use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\DateTime\RangeType;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\Number\IntegerRange;

final class IntegerRangeTest extends TestCase
{
    public function test_閉区間で有効な範囲を作成できる(): void
    {
        // Arrange
        $from = 1;
        $to = 10;

        // Act
        $range = IntegerRange::closed($from, $to);

        // Assert
        $this->assertSame($from, $range->getFrom());
        $this->assertSame($to, $range->getTo());
        $this->assertSame(RangeType::CLOSED, $range->getRangeType());
        $this->assertSame('[1, 10]', (string)$range);
    }

    public function test_開区間で有効な範囲を作成できる(): void
    {
        // Arrange
        $from = 1;
        $to = 10;

        // Act
        $range = IntegerRange::open($from, $to);

        // Assert
        $this->assertSame(RangeType::OPEN, $range->getRangeType());
        $this->assertSame('(1, 10)', (string)$range);
    }

    public function test_半開区間で有効な範囲を作成できる(): void
    {
        // Arrange
        $from = 1;
        $to = 10;

        // Act
        $rangeLeft = IntegerRange::halfOpenLeft($from, $to);
        $rangeRight = IntegerRange::halfOpenRight($from, $to);

        // Assert
        $this->assertSame(RangeType::HALF_OPEN_LEFT, $rangeLeft->getRangeType());
        $this->assertSame('(1, 10]', (string)$rangeLeft);
        $this->assertSame(RangeType::HALF_OPEN_RIGHT, $rangeRight->getRangeType());
        $this->assertSame('[1, 10)', (string)$rangeRight);
    }

    public function test_開始値が終了値より大きい場合エラーになる(): void
    {
        // Arrange
        $from = 10;
        $to = 5;

        // Act
        $result = IntegerRange::tryFrom($from, $to);

        // Assert
        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);
        $this->assertSame('value_object.integer_range.invalid_range', $error->getCode());
        $this->assertSame('開始値は終了値以下である必要があります', $error->getMessage());
    }

    public function test_contains_閉区間の境界値を含む(): void
    {
        // Arrange
        $from = 1;
        $to = 10;
        $range = IntegerRange::closed($from, $to);

        // Act & Assert
        $this->assertTrue($range->contains($from)); // 開始境界
        $this->assertTrue($range->contains($to)); // 終了境界
        $this->assertTrue($range->contains(5)); // 中間
        $this->assertFalse($range->contains(0)); // 範囲前
        $this->assertFalse($range->contains(11)); // 範囲後
    }

    public function test_contains_開区間の境界値を含まない(): void
    {
        // Arrange
        $from = 1;
        $to = 10;
        $range = IntegerRange::open($from, $to);

        // Act & Assert
        $this->assertFalse($range->contains($from)); // 開始境界
        $this->assertFalse($range->contains($to)); // 終了境界
        $this->assertTrue($range->contains(5)); // 中間
        $this->assertFalse($range->contains(0)); // 範囲前
        $this->assertFalse($range->contains(11)); // 範囲後
    }

    public function test_contains_半開区間の境界値(): void
    {
        // Arrange
        $from = 1;
        $to = 10;

        // Act & Assert
        // 左開区間
        $rangeLeft = IntegerRange::halfOpenLeft($from, $to);
        $this->assertFalse($rangeLeft->contains($from)); // 開始境界（含まない）
        $this->assertTrue($rangeLeft->contains($to)); // 終了境界（含む）

        // 右開区間
        $rangeRight = IntegerRange::halfOpenRight($from, $to);
        $this->assertTrue($rangeRight->contains($from)); // 開始境界（含む）
        $this->assertFalse($rangeRight->contains($to)); // 終了境界（含まない）
    }

    public function test_overlaps_重なりがある範囲(): void
    {
        // Arrange
        $range1 = IntegerRange::closed(1, 5);
        $range2 = IntegerRange::closed(3, 8);

        // Act & Assert
        $this->assertTrue($range1->overlaps($range2));
        $this->assertTrue($range2->overlaps($range1));
    }

    public function test_overlaps_重なりがない範囲(): void
    {
        // Arrange
        $range1 = IntegerRange::closed(1, 5);
        $range2 = IntegerRange::closed(10, 15);

        // Act & Assert
        $this->assertFalse($range1->overlaps($range2));
        $this->assertFalse($range2->overlaps($range1));
    }

    public function test_overlaps_境界で接する範囲_閉区間(): void
    {
        // Arrange
        $range1 = IntegerRange::closed(1, 5);
        $range2 = IntegerRange::closed(5, 10);

        // Act & Assert
        $this->assertTrue($range1->overlaps($range2)); // 境界で接触
        $this->assertTrue($range2->overlaps($range1));
    }

    public function test_overlaps_境界で接する範囲_開区間(): void
    {
        // Arrange
        $range1 = IntegerRange::open(1, 5);
        $range2 = IntegerRange::open(5, 10);

        // Act & Assert
        $this->assertFalse($range1->overlaps($range2)); // 開区間では境界での接触は重なりとみなさない
        $this->assertFalse($range2->overlaps($range1));
    }

    public function test_overlaps_境界で接する範囲_半開区間(): void
    {
        // Arrange
        $range1 = IntegerRange::halfOpenRight(1, 5); // [1, 5)
        $range2 = IntegerRange::halfOpenLeft(5, 10); // (5, 10]

        // Act & Assert
        $this->assertFalse($range1->overlaps($range2)); // 5は片方にしか含まれない
        $this->assertFalse($range2->overlaps($range1));
    }

    public function test_count_閉区間の要素数計算(): void
    {
        // Arrange
        $from = 1;
        $to = 5;
        $range = IntegerRange::closed($from, $to);

        // Act
        $count = $range->count();

        // Assert
        $this->assertSame(5, $count); // 1から5まで（両端含む）= 5個
    }

    public function test_count_開区間の要素数計算(): void
    {
        // Arrange
        $from = 1;
        $to = 5;
        $range = IntegerRange::open($from, $to);

        // Act
        $count = $range->count();

        // Assert
        $this->assertSame(3, $count); // 1と5を含まない = 3個（2、3、4）
    }

    public function test_count_半開区間の要素数計算(): void
    {
        // Arrange
        $from = 1;
        $to = 5;

        // Act
        $countLeft = IntegerRange::halfOpenLeft($from, $to)->count();
        $countRight = IntegerRange::halfOpenRight($from, $to)->count();

        // Assert
        $this->assertSame(4, $countLeft); // 1を含まず、5を含む = 4個
        $this->assertSame(4, $countRight); // 1を含み、5を含まない = 4個
    }

    public function test_count_境界値が同じ場合(): void
    {
        // Arrange & Act & Assert
        $this->assertSame(1, IntegerRange::closed(5, 5)->count()); // [5, 5] = 1個
        $this->assertSame(0, IntegerRange::open(5, 5)->count()); // (5, 5) = 0個
        $this->assertSame(0, IntegerRange::halfOpenLeft(5, 5)->count()); // (5, 5] = 0個
        $this->assertSame(0, IntegerRange::halfOpenRight(5, 5)->count()); // [5, 5) = 0個
    }

    public function test_count_開区間で要素数が少ない場合(): void
    {
        // Arrange & Act & Assert
        $this->assertSame(0, IntegerRange::open(1, 2)->count()); // (1, 2) = 0個
        $this->assertSame(1, IntegerRange::open(1, 3)->count()); // (1, 3) = 1個（2のみ）
    }

    public function test_iterate_閉区間での整数の反復(): void
    {
        // Arrange
        $from = 1;
        $to = 3;
        $range = IntegerRange::closed($from, $to);

        // Act
        $values = [];
        foreach ($range->iterate() as $value) {
            $values[] = $value;
        }

        // Assert
        $this->assertSame([1, 2, 3], $values);
    }

    public function test_iterate_開区間での整数の反復(): void
    {
        // Arrange
        $from = 1;
        $to = 5;
        $range = IntegerRange::open($from, $to);

        // Act
        $values = [];
        foreach ($range->iterate() as $value) {
            $values[] = $value;
        }

        // Assert
        $this->assertSame([2, 3, 4], $values);
    }

    public function test_iterate_半開区間での整数の反復(): void
    {
        // Arrange
        $from = 1;
        $to = 5;

        // Act
        $valuesLeft = [];
        foreach (IntegerRange::halfOpenLeft($from, $to)->iterate() as $value) {
            $valuesLeft[] = $value;
        }

        $valuesRight = [];
        foreach (IntegerRange::halfOpenRight($from, $to)->iterate() as $value) {
            $valuesRight[] = $value;
        }

        // Assert
        $this->assertSame([2, 3, 4, 5], $valuesLeft);
        $this->assertSame([1, 2, 3, 4], $valuesRight);
    }

    public function test_iterate_要素がない場合(): void
    {
        // Arrange
        $range = IntegerRange::open(1, 2);

        // Act
        $values = [];
        foreach ($range->iterate() as $value) {
            $values[] = $value;
        }

        // Assert
        $this->assertSame([], $values);
    }

    public function test_equals_同じ範囲の場合trueを返す(): void
    {
        // Arrange
        $range1 = IntegerRange::closed(1, 10);
        $range2 = IntegerRange::closed(1, 10);

        // Act & Assert
        $this->assertTrue($range1->equals($range2));
        $this->assertTrue($range2->equals($range1));
    }

    public function test_equals_異なる範囲の場合falseを返す(): void
    {
        // Arrange
        $range1 = IntegerRange::closed(1, 10);
        $range2 = IntegerRange::closed(1, 11);
        $range3 = IntegerRange::open(1, 10);

        // Act & Assert
        $this->assertFalse($range1->equals($range2));
        $this->assertFalse($range1->equals($range3));
    }

    public function test_jsonSerialize_正しいJSON形式を返す(): void
    {
        // Arrange
        $range = IntegerRange::halfOpenRight(1, 10);

        // Act
        $json = $range->jsonSerialize();

        // Assert
        $this->assertSame([
            'from' => 1,
            'to' => 10,
            'rangeType' => 'half_open_right',
        ], $json);
    }


    public function test_fromNullable_有効な値が渡された場合Someを返す(): void
    {
        // Act
        $result = IntegerRange::fromNullable(1, 10);

        // Assert
        $this->assertTrue($result->isSome());
        $range = $result->unwrap();
        $this->assertSame(1, $range->getFrom());
        $this->assertSame(10, $range->getTo());
    }


    public function test_tryFromNullable_無効な値が渡された場合Errを返す(): void
    {
        // Act
        $result = IntegerRange::tryFromNullable(10, 5);

        // Assert
        $this->assertTrue($result->isErr());
    }

    public function test_tryFromNullable_有効な値が渡された場合Ok_Someを返す(): void
    {
        // Act
        $result = IntegerRange::tryFromNullable(1, 10);

        // Assert
        $this->assertTrue($result->isOk());
        $option = $result->unwrap();
        $this->assertTrue($option->isSome());
        $range = $option->unwrap();
        $this->assertSame(1, $range->getFrom());
        $this->assertSame(10, $range->getTo());
    }

    public function test_負の整数範囲も作成できる(): void
    {
        // Arrange
        $from = -10;
        $to = -1;

        // Act
        $range = IntegerRange::closed($from, $to);

        // Assert
        $this->assertSame($from, $range->getFrom());
        $this->assertSame($to, $range->getTo());
        $this->assertSame(10, $range->count());
    }

    public function test_ゼロを含む範囲も作成できる(): void
    {
        // Arrange
        $from = -5;
        $to = 5;

        // Act
        $range = IntegerRange::closed($from, $to);

        // Assert
        $this->assertTrue($range->contains(0));
        $this->assertSame(11, $range->count());
    }

    public function test_toがnullの場合は最大値が設定される(): void
    {
        // Act
        $range = IntegerRange::closed(1, null);

        // Assert
        $this->assertSame(1, $range->getFrom());
        $this->assertSame(PHP_INT_MAX, $range->getTo());
    }

    public function test_各ファクトリメソッドでtoがnullの場合は最大値が設定される(): void
    {
        // Act
        $closed = IntegerRange::closed(1, null);
        $open = IntegerRange::open(1, null);
        $halfOpenLeft = IntegerRange::halfOpenLeft(1, null);
        $halfOpenRight = IntegerRange::halfOpenRight(1, null);

        // Assert
        $this->assertSame(PHP_INT_MAX, $closed->getTo());
        $this->assertSame(PHP_INT_MAX, $open->getTo());
        $this->assertSame(PHP_INT_MAX, $halfOpenLeft->getTo());
        $this->assertSame(PHP_INT_MAX, $halfOpenRight->getTo());
    }

    public function test_fromNullable_fromがnullの場合のみNoneを返す(): void
    {
        // Act
        $result1 = IntegerRange::fromNullable(null, 10);
        $result2 = IntegerRange::fromNullable(1, null);
        $result3 = IntegerRange::fromNullable(null, null);

        // Assert
        $this->assertTrue($result1->isNone());
        $this->assertTrue($result2->isSome());
        $this->assertTrue($result3->isNone());

        // toがnullの場合は最大値が設定される
        $range = $result2->unwrap();
        $this->assertSame(1, $range->getFrom());
        $this->assertSame(PHP_INT_MAX, $range->getTo());
    }

    public function test_tryFromNullable_fromがnullの場合のみNoneを返す(): void
    {
        // Act
        $result1 = IntegerRange::tryFromNullable(null, 10);
        $result2 = IntegerRange::tryFromNullable(1, null);

        // Assert
        $this->assertTrue($result1->isOk());
        $option1 = $result1->unwrap();
        $this->assertTrue($option1->isNone());

        $this->assertTrue($result2->isOk());
        $option2 = $result2->unwrap();
        $this->assertTrue($option2->isSome());

        // toがnullの場合は最大値が設定される
        $range = $option2->unwrap();
        $this->assertSame(1, $range->getFrom());
        $this->assertSame(PHP_INT_MAX, $range->getTo());
    }
}
