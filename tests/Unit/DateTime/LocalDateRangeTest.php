<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\DateTime;

use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\DateTime\LocalDate;
use WizDevelop\PhpValueObject\DateTime\LocalDateRange;
use WizDevelop\PhpValueObject\DateTime\RangeType;
use WizDevelop\PhpValueObject\Error\ValueObjectError;

final class LocalDateRangeTest extends TestCase
{
    public function test_閉区間で有効な範囲を作成できる(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);

        // Act
        $range = LocalDateRange::from($from, $to, RangeType::CLOSED);

        // Assert
        $this->assertSame($from, $range->getFrom());
        $this->assertSame($to, $range->getTo());
        $this->assertSame(RangeType::CLOSED, $range->getRangeType());
        $this->assertSame('[2024-01-01, 2024-01-31]', $range->toISOString());
    }

    public function test_開区間で有効な範囲を作成できる(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);

        // Act
        $range = LocalDateRange::from($from, $to, RangeType::OPEN);

        // Assert
        $this->assertSame(RangeType::OPEN, $range->getRangeType());
        $this->assertSame('(2024-01-01, 2024-01-31)', $range->toISOString());
    }

    public function test_半開区間で有効な範囲を作成できる(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);

        // Act
        $rangeLeft = LocalDateRange::from($from, $to, RangeType::HALF_OPEN_LEFT);
        $rangeRight = LocalDateRange::from($from, $to, RangeType::HALF_OPEN_RIGHT);

        // Assert
        $this->assertSame(RangeType::HALF_OPEN_LEFT, $rangeLeft->getRangeType());
        $this->assertSame('(2024-01-01, 2024-01-31]', $rangeLeft->toISOString());
        $this->assertSame(RangeType::HALF_OPEN_RIGHT, $rangeRight->getRangeType());
        $this->assertSame('[2024-01-01, 2024-01-31)', $rangeRight->toISOString());
    }

    public function test_開始日付が終了日付より後の場合エラーになる(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 31);
        $to = LocalDate::of(2024, 1, 30);

        // Act
        $result = LocalDateRange::tryFrom($from, $to);

        // Assert
        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);
        $this->assertSame('value_object.date_range.invalid_range', $error->getCode());
        $this->assertSame('開始日付は終了日付以前である必要があります', $error->getMessage());
    }

    public function test_contains_閉区間の境界値を含む(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);
        $range = LocalDateRange::from($from, $to, RangeType::CLOSED);

        // Act & Assert
        $this->assertTrue($range->contains($from)); // 開始境界
        $this->assertTrue($range->contains($to)); // 終了境界
        $this->assertTrue($range->contains(LocalDate::of(2024, 1, 15))); // 中間
        $this->assertFalse($range->contains(LocalDate::of(2023, 12, 31))); // 範囲前
        $this->assertFalse($range->contains(LocalDate::of(2024, 2, 1))); // 範囲後
    }

    public function test_contains_開区間の境界値を含まない(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);
        $range = LocalDateRange::from($from, $to, RangeType::OPEN);

        // Act & Assert
        $this->assertFalse($range->contains($from)); // 開始境界
        $this->assertFalse($range->contains($to)); // 終了境界
        $this->assertTrue($range->contains(LocalDate::of(2024, 1, 15))); // 中間
    }

    public function test_contains_半開区間の境界値(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);

        // Act & Assert
        // 左開区間
        $rangeLeft = LocalDateRange::from($from, $to, RangeType::HALF_OPEN_LEFT);
        $this->assertFalse($rangeLeft->contains($from)); // 開始境界（含まない）
        $this->assertTrue($rangeLeft->contains($to)); // 終了境界（含む）

        // 右開区間
        $rangeRight = LocalDateRange::from($from, $to, RangeType::HALF_OPEN_RIGHT);
        $this->assertTrue($rangeRight->contains($from)); // 開始境界（含む）
        $this->assertFalse($rangeRight->contains($to)); // 終了境界（含まない）
    }

    public function test_overlaps_重なりがある範囲(): void
    {
        // Arrange
        $range1 = LocalDateRange::from(
            LocalDate::of(2024, 1, 1),
            LocalDate::of(2024, 1, 15),
            RangeType::CLOSED
        );
        $range2 = LocalDateRange::from(
            LocalDate::of(2024, 1, 10),
            LocalDate::of(2024, 1, 20),
            RangeType::CLOSED
        );

        // Act & Assert
        $this->assertTrue($range1->overlaps($range2));
        $this->assertTrue($range2->overlaps($range1));
    }

    public function test_overlaps_重なりがない範囲(): void
    {
        // Arrange
        $range1 = LocalDateRange::from(
            LocalDate::of(2024, 1, 1),
            LocalDate::of(2024, 1, 10),
            RangeType::CLOSED
        );
        $range2 = LocalDateRange::from(
            LocalDate::of(2024, 1, 20),
            LocalDate::of(2024, 1, 31),
            RangeType::CLOSED
        );

        // Act & Assert
        $this->assertFalse($range1->overlaps($range2));
        $this->assertFalse($range2->overlaps($range1));
    }

    public function test_overlaps_境界で接する範囲_閉区間(): void
    {
        // Arrange
        $range1 = LocalDateRange::from(
            LocalDate::of(2024, 1, 1),
            LocalDate::of(2024, 1, 15),
            RangeType::CLOSED
        );
        $range2 = LocalDateRange::from(
            LocalDate::of(2024, 1, 15),
            LocalDate::of(2024, 1, 31),
            RangeType::CLOSED
        );

        // Act & Assert
        $this->assertTrue($range1->overlaps($range2)); // 境界で接触
        $this->assertTrue($range2->overlaps($range1));
    }

    public function test_overlaps_境界で接する範囲_開区間(): void
    {
        // Arrange
        $range1 = LocalDateRange::from(
            LocalDate::of(2024, 1, 1),
            LocalDate::of(2024, 1, 15),
            RangeType::OPEN
        );
        $range2 = LocalDateRange::from(
            LocalDate::of(2024, 1, 15),
            LocalDate::of(2024, 1, 31),
            RangeType::OPEN
        );

        // Act & Assert
        $this->assertFalse($range1->overlaps($range2)); // 開区間では境界での接触は重なりとみなさない
        $this->assertFalse($range2->overlaps($range1));
    }

    public function test_overlaps_境界で接する範囲_半開区間の組み合わせ(): void
    {
        // Arrange
        // 左開区間と右開区間
        $range1 = LocalDateRange::from(
            LocalDate::of(2024, 1, 1),
            LocalDate::of(2024, 1, 15),
            RangeType::HALF_OPEN_LEFT
        );
        $range2 = LocalDateRange::from(
            LocalDate::of(2024, 1, 15),
            LocalDate::of(2024, 1, 31),
            RangeType::HALF_OPEN_RIGHT
        );

        // Act & Assert
        $this->assertTrue($range1->overlaps($range2)); // 1つ目の終了（含む）と2つ目の開始（含む）が重なる
        $this->assertTrue($range2->overlaps($range1));

        // 右開区間と左開区間
        $range3 = LocalDateRange::from(
            LocalDate::of(2024, 2, 1),
            LocalDate::of(2024, 2, 15),
            RangeType::HALF_OPEN_RIGHT
        );
        $range4 = LocalDateRange::from(
            LocalDate::of(2024, 2, 15),
            LocalDate::of(2024, 2, 28),
            RangeType::HALF_OPEN_LEFT
        );

        // Act & Assert
        $this->assertFalse($range3->overlaps($range4)); // 1つ目の終了（含まない）と2つ目の開始（含まない）で重ならない
        $this->assertFalse($range4->overlaps($range3));
    }

    public function test_overlaps_一方が他方を完全に含む範囲(): void
    {
        // Arrange
        $range1 = LocalDateRange::from(
            LocalDate::of(2024, 1, 1),
            LocalDate::of(2024, 1, 31),
            RangeType::CLOSED
        );
        $range2 = LocalDateRange::from(
            LocalDate::of(2024, 1, 10),
            LocalDate::of(2024, 1, 20),
            RangeType::CLOSED
        );

        // Act & Assert
        $this->assertTrue($range1->overlaps($range2)); // range1がrange2を完全に含む
        $this->assertTrue($range2->overlaps($range1));

        // 開区間の場合
        $range3 = LocalDateRange::from(
            LocalDate::of(2024, 2, 1),
            LocalDate::of(2024, 2, 28),
            RangeType::OPEN
        );
        $range4 = LocalDateRange::from(
            LocalDate::of(2024, 2, 10),
            LocalDate::of(2024, 2, 20),
            RangeType::OPEN
        );

        $this->assertTrue($range3->overlaps($range4));
        $this->assertTrue($range4->overlaps($range3));
    }

    public function test_overlaps_同一範囲(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);
        $range1 = LocalDateRange::from($from, $to, RangeType::CLOSED);
        $range2 = LocalDateRange::from($from, $to, RangeType::CLOSED);

        // Act & Assert
        $this->assertTrue($range1->overlaps($range2));
        $this->assertTrue($range2->overlaps($range1));

        // 異なる区間タイプでも同じ期間
        $range3 = LocalDateRange::from($from, $to, RangeType::OPEN);
        $range4 = LocalDateRange::from($from, $to, RangeType::HALF_OPEN_RIGHT);

        $this->assertTrue($range3->overlaps($range4));
        $this->assertTrue($range4->overlaps($range3));
    }

    public function test_days_閉区間の日数計算(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 5);
        $range = LocalDateRange::from($from, $to, RangeType::CLOSED);

        // Act
        $days = $range->days();

        // Assert
        $this->assertSame(5, $days); // 1日から5日まで（両端含む）= 5日間
    }

    public function test_days_開区間の日数計算(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 5);
        $range = LocalDateRange::from($from, $to, RangeType::OPEN);

        // Act
        $days = $range->days();

        // Assert
        $this->assertSame(3, $days); // 1日と5日を含まない = 3日間（2日、3日、4日）
    }

    public function test_days_半開区間の日数計算(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 5);

        // Act
        $daysLeft = LocalDateRange::from($from, $to, RangeType::HALF_OPEN_LEFT)->days();
        $daysRight = LocalDateRange::from($from, $to, RangeType::HALF_OPEN_RIGHT)->days();

        // Assert
        $this->assertSame(4, $daysLeft); // 1日を含まず、5日を含む = 4日間
        $this->assertSame(4, $daysRight); // 1日を含み、5日を含まない = 4日間
    }

    public function test_iterate_閉区間での日付の反復(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 3);
        $range = LocalDateRange::from($from, $to, RangeType::CLOSED);

        // Act
        $dates = [];
        foreach ($range->getIterator() as $date) {
            $dates[] = $date->toISOString();
        }

        // Assert
        $this->assertSame(['2024-01-01', '2024-01-02', '2024-01-03'], $dates);
    }

    public function test_iterate_開区間での日付の反復(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 5);
        $range = LocalDateRange::from($from, $to, RangeType::OPEN);

        // Act
        $dates = [];
        foreach ($range->getIterator() as $date) {
            $dates[] = $date->toISOString();
        }

        // Assert
        $this->assertSame(['2024-01-02', '2024-01-03', '2024-01-04'], $dates);
    }

    public function test_equals_同じ範囲(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);
        $range1 = LocalDateRange::from($from, $to, RangeType::CLOSED);
        $range2 = LocalDateRange::from($from, $to, RangeType::CLOSED);

        // Act & Assert
        $this->assertTrue($range1->equals($range2));
    }

    public function test_equals_異なる範囲(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);
        $range1 = LocalDateRange::from($from, $to, RangeType::CLOSED);
        $range2 = LocalDateRange::from($from, $to, RangeType::OPEN);

        // Act & Assert
        $this->assertFalse($range1->equals($range2)); // 範囲タイプが異なる
    }

    public function test_jsonSerialize(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);
        $range = LocalDateRange::from($from, $to, RangeType::CLOSED);

        // Act
        $json = $range->jsonSerialize();

        // Assert
        $this->assertSame('[2024-01-01, 2024-01-31]', $json);
    }

    public function test_from_デフォルトは右開区間(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);

        // Act
        $range = LocalDateRange::from($from, $to);

        // Assert
        $this->assertSame(RangeType::HALF_OPEN_RIGHT, $range->getRangeType());
        $this->assertSame('[2024-01-01, 2024-01-31)', $range->toISOString());
    }

    public function test_withFrom_新しい開始日付で範囲を作成(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);
        $newFrom = LocalDate::of(2024, 1, 15);
        $range = LocalDateRange::from($from, $to, RangeType::CLOSED);

        // Act
        $newRange = $range->withFrom($newFrom);

        // Assert
        $this->assertSame($newFrom, $newRange->getFrom());
        $this->assertSame($to, $newRange->getTo());
        $this->assertSame(RangeType::CLOSED, $newRange->getRangeType());
        $this->assertSame('[2024-01-15, 2024-01-31]', $newRange->toISOString());
        // 元の範囲は変更されていない
        $this->assertSame($from, $range->getFrom());
    }

    public function test_withFrom_無効な範囲の場合例外が発生(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 15);
        $newFrom = LocalDate::of(2024, 1, 20); // toより後の日付
        $range = LocalDateRange::from($from, $to);

        // Act & Assert
        $this->expectException(\AssertionError::class);
        $range->withFrom($newFrom);
    }

    public function test_withTo_新しい終了日付で範囲を作成(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);
        $newTo = LocalDate::of(2024, 1, 15);
        $range = LocalDateRange::from($from, $to, RangeType::CLOSED);

        // Act
        $newRange = $range->withTo($newTo);

        // Assert
        $this->assertSame($from, $newRange->getFrom());
        $this->assertSame($newTo, $newRange->getTo());
        $this->assertSame(RangeType::CLOSED, $newRange->getRangeType());
        $this->assertSame('[2024-01-01, 2024-01-15]', $newRange->toISOString());
        // 元の範囲は変更されていない
        $this->assertSame($to, $range->getTo());
    }

    public function test_withTo_無効な範囲の場合例外が発生(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 20);
        $to = LocalDate::of(2024, 1, 31);
        $newTo = LocalDate::of(2024, 1, 15); // fromより前の日付
        $range = LocalDateRange::from($from, $to);

        // Act & Assert
        $this->expectException(\AssertionError::class);
        $range->withTo($newTo);
    }

    public function test_tryWithFrom_有効な開始日付の場合成功(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);
        $newFrom = LocalDate::of(2024, 1, 15);
        $range = LocalDateRange::from($from, $to, RangeType::CLOSED);

        // Act
        $result = $range->tryWithFrom($newFrom);

        // Assert
        $this->assertTrue($result->isOk());
        $newRange = $result->unwrap();
        $this->assertSame($newFrom, $newRange->getFrom());
        $this->assertSame($to, $newRange->getTo());
        $this->assertSame(RangeType::CLOSED, $newRange->getRangeType());
    }

    public function test_tryWithFrom_無効な開始日付の場合エラー(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 15);
        $newFrom = LocalDate::of(2024, 1, 20); // toより後の日付
        $range = LocalDateRange::from($from, $to);

        // Act
        $result = $range->tryWithFrom($newFrom);

        // Assert
        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);
        $this->assertSame('value_object.date_range.invalid_range', $error->getCode());
        $this->assertSame('開始日付は終了日付以前である必要があります', $error->getMessage());
    }

    public function test_tryWithTo_有効な終了日付の場合成功(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);
        $newTo = LocalDate::of(2024, 1, 15);
        $range = LocalDateRange::from($from, $to, RangeType::CLOSED);

        // Act
        $result = $range->tryWithTo($newTo);

        // Assert
        $this->assertTrue($result->isOk());
        $newRange = $result->unwrap();
        $this->assertSame($from, $newRange->getFrom());
        $this->assertSame($newTo, $newRange->getTo());
        $this->assertSame(RangeType::CLOSED, $newRange->getRangeType());
    }

    public function test_tryWithTo_無効な終了日付の場合エラー(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 20);
        $to = LocalDate::of(2024, 1, 31);
        $newTo = LocalDate::of(2024, 1, 15); // fromより前の日付
        $range = LocalDateRange::from($from, $to);

        // Act
        $result = $range->tryWithTo($newTo);

        // Assert
        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);
        $this->assertSame('value_object.date_range.invalid_range', $error->getCode());
        $this->assertSame('開始日付は終了日付以前である必要があります', $error->getMessage());
    }

    public function test_strictlyBefore_完全に前にある範囲(): void
    {
        // Arrange
        $range1 = LocalDateRange::from(
            LocalDate::of(2024, 1, 1),
            LocalDate::of(2024, 1, 10),
            RangeType::CLOSED
        );
        $range2 = LocalDateRange::from(
            LocalDate::of(2024, 1, 20),
            LocalDate::of(2024, 1, 31),
            RangeType::CLOSED
        );

        // Act & Assert
        $this->assertTrue($range1->strictlyBefore($range2));
        $this->assertFalse($range2->strictlyBefore($range1));
    }

    public function test_strictlyBefore_境界で接する範囲_閉区間(): void
    {
        // Arrange
        $range1 = LocalDateRange::from(
            LocalDate::of(2024, 1, 1),
            LocalDate::of(2024, 1, 15),
            RangeType::CLOSED
        );
        $range2 = LocalDateRange::from(
            LocalDate::of(2024, 1, 15),
            LocalDate::of(2024, 1, 31),
            RangeType::CLOSED
        );

        // Act & Assert
        $this->assertFalse($range1->strictlyBefore($range2)); // 境界で接触しているため
        $this->assertFalse($range2->strictlyBefore($range1));
    }

    public function test_strictlyBefore_境界で接する範囲_開区間(): void
    {
        // Arrange
        $range1 = LocalDateRange::from(
            LocalDate::of(2024, 1, 1),
            LocalDate::of(2024, 1, 15),
            RangeType::OPEN
        );
        $range2 = LocalDateRange::from(
            LocalDate::of(2024, 1, 15),
            LocalDate::of(2024, 1, 31),
            RangeType::OPEN
        );

        // Act & Assert
        $this->assertTrue($range1->strictlyBefore($range2)); // 開区間では境界での接触も完全に前
        $this->assertFalse($range2->strictlyBefore($range1));
    }

    public function test_strictlyBefore_境界で接する範囲_右開区間と左開区間(): void
    {
        // Arrange
        $range1 = LocalDateRange::from(
            LocalDate::of(2024, 1, 1),
            LocalDate::of(2024, 1, 15),
            RangeType::HALF_OPEN_RIGHT
        );
        $range2 = LocalDateRange::from(
            LocalDate::of(2024, 1, 15),
            LocalDate::of(2024, 1, 31),
            RangeType::HALF_OPEN_LEFT
        );

        // Act & Assert
        $this->assertTrue($range1->strictlyBefore($range2)); // 両方の境界が開いているため
        $this->assertFalse($range2->strictlyBefore($range1));
    }

    public function test_strictlyBefore_重なる範囲(): void
    {
        // Arrange
        $range1 = LocalDateRange::from(
            LocalDate::of(2024, 1, 1),
            LocalDate::of(2024, 1, 20),
            RangeType::CLOSED
        );
        $range2 = LocalDateRange::from(
            LocalDate::of(2024, 1, 10),
            LocalDate::of(2024, 1, 31),
            RangeType::CLOSED
        );

        // Act & Assert
        $this->assertFalse($range1->strictlyBefore($range2));
        $this->assertFalse($range2->strictlyBefore($range1));
    }

    public function test_strictlyBefore_境界で接する範囲_左開区間と右開区間の逆パターン(): void
    {
        // Arrange
        $range1 = LocalDateRange::from(
            LocalDate::of(2024, 1, 1),
            LocalDate::of(2024, 1, 15),
            RangeType::HALF_OPEN_LEFT
        );
        $range2 = LocalDateRange::from(
            LocalDate::of(2024, 1, 15),
            LocalDate::of(2024, 1, 31),
            RangeType::HALF_OPEN_RIGHT
        );

        // Act & Assert
        $this->assertFalse($range1->strictlyBefore($range2)); // 左開区間の終了（含む）と右開区間の開始（含む）が接触
        $this->assertFalse($range2->strictlyBefore($range1));
    }

    public function test_strictlyBefore_閉区間と開区間の混在(): void
    {
        // Arrange
        // 閉区間の後に開区間
        $range1 = LocalDateRange::from(
            LocalDate::of(2024, 1, 1),
            LocalDate::of(2024, 1, 15),
            RangeType::CLOSED
        );
        $range2 = LocalDateRange::from(
            LocalDate::of(2024, 1, 15),
            LocalDate::of(2024, 1, 31),
            RangeType::OPEN
        );

        // Act & Assert
        $this->assertTrue($range1->strictlyBefore($range2)); // 閉区間の終了（含む）と開区間の開始（含まない）
        $this->assertFalse($range2->strictlyBefore($range1));

        // 開区間の後に閉区間
        $range3 = LocalDateRange::from(
            LocalDate::of(2024, 2, 1),
            LocalDate::of(2024, 2, 15),
            RangeType::OPEN
        );
        $range4 = LocalDateRange::from(
            LocalDate::of(2024, 2, 15),
            LocalDate::of(2024, 2, 28),
            RangeType::CLOSED
        );

        // Act & Assert
        $this->assertTrue($range3->strictlyBefore($range4)); // 開区間の終了（含まない）と閉区間の開始（含む）
        $this->assertFalse($range4->strictlyBefore($range3));
    }

    public function test_strictlyBefore_閉区間と半開区間の混在(): void
    {
        // Arrange
        // 閉区間の後に左開区間
        $range1 = LocalDateRange::from(
            LocalDate::of(2024, 1, 1),
            LocalDate::of(2024, 1, 15),
            RangeType::CLOSED
        );
        $range2 = LocalDateRange::from(
            LocalDate::of(2024, 1, 15),
            LocalDate::of(2024, 1, 31),
            RangeType::HALF_OPEN_LEFT
        );

        // Act & Assert
        $this->assertTrue($range1->strictlyBefore($range2)); // 閉区間の終了（含む）と左開区間の開始（含まない）
        $this->assertFalse($range2->strictlyBefore($range1));

        // 右開区間の後に閉区間
        $range3 = LocalDateRange::from(
            LocalDate::of(2024, 2, 1),
            LocalDate::of(2024, 2, 15),
            RangeType::HALF_OPEN_RIGHT
        );
        $range4 = LocalDateRange::from(
            LocalDate::of(2024, 2, 15),
            LocalDate::of(2024, 2, 28),
            RangeType::CLOSED
        );

        // Act & Assert
        $this->assertTrue($range3->strictlyBefore($range4)); // 右開区間の終了（含まない）と閉区間の開始（含む）
        $this->assertFalse($range4->strictlyBefore($range3));
    }

    public function test_count_閉区間の要素数(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 5);
        $range = LocalDateRange::from($from, $to, RangeType::CLOSED);

        // Act
        $count = $range->count();

        // Assert
        $this->assertSame(5, $count); // 1日から5日まで（両端含む）= 5要素
    }

    public function test_count_同じ日付の範囲(): void
    {
        // Arrange
        $date = LocalDate::of(2024, 1, 1);
        $range = LocalDateRange::from($date, $date, RangeType::CLOSED);

        // Act
        $count = $range->count();

        // Assert
        $this->assertSame(1, $count); // 単一の日付 = 1要素
    }

    public function test_count_年をまたぐ範囲(): void
    {
        // Arrange
        $from = LocalDate::of(2023, 12, 30);
        $to = LocalDate::of(2024, 1, 2);
        $range = LocalDateRange::from($from, $to, RangeType::CLOSED);

        // Act
        $count = $range->count();

        // Assert
        $this->assertSame(4, $count); // 12/30, 12/31, 1/1, 1/2 = 4要素
    }

    public function test_count_大きな範囲(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 12, 31);
        $range = LocalDateRange::from($from, $to, RangeType::CLOSED);

        // Act
        $count = $range->count();

        // Assert
        $this->assertSame(366, $count); // 2024年は閏年で366日
    }
}
