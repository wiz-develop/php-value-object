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
        $range = LocalDateRange::closed($from, $to);

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
        $range = LocalDateRange::open($from, $to);

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
        $rangeLeft = LocalDateRange::halfOpenLeft($from, $to);
        $rangeRight = LocalDateRange::halfOpenRight($from, $to);

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
        $range = LocalDateRange::closed($from, $to);

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
        $range = LocalDateRange::open($from, $to);

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
        $rangeLeft = LocalDateRange::halfOpenLeft($from, $to);
        $this->assertFalse($rangeLeft->contains($from)); // 開始境界（含まない）
        $this->assertTrue($rangeLeft->contains($to)); // 終了境界（含む）

        // 右開区間
        $rangeRight = LocalDateRange::halfOpenRight($from, $to);
        $this->assertTrue($rangeRight->contains($from)); // 開始境界（含む）
        $this->assertFalse($rangeRight->contains($to)); // 終了境界（含まない）
    }

    public function test_overlaps_重なりがある範囲(): void
    {
        // Arrange
        $range1 = LocalDateRange::closed(
            LocalDate::of(2024, 1, 1),
            LocalDate::of(2024, 1, 15)
        );
        $range2 = LocalDateRange::closed(
            LocalDate::of(2024, 1, 10),
            LocalDate::of(2024, 1, 20)
        );

        // Act & Assert
        $this->assertTrue($range1->overlaps($range2));
        $this->assertTrue($range2->overlaps($range1));
    }

    public function test_overlaps_重なりがない範囲(): void
    {
        // Arrange
        $range1 = LocalDateRange::closed(
            LocalDate::of(2024, 1, 1),
            LocalDate::of(2024, 1, 10)
        );
        $range2 = LocalDateRange::closed(
            LocalDate::of(2024, 1, 20),
            LocalDate::of(2024, 1, 31)
        );

        // Act & Assert
        $this->assertFalse($range1->overlaps($range2));
        $this->assertFalse($range2->overlaps($range1));
    }

    public function test_overlaps_境界で接する範囲_閉区間(): void
    {
        // Arrange
        $range1 = LocalDateRange::closed(
            LocalDate::of(2024, 1, 1),
            LocalDate::of(2024, 1, 15)
        );
        $range2 = LocalDateRange::closed(
            LocalDate::of(2024, 1, 15),
            LocalDate::of(2024, 1, 31)
        );

        // Act & Assert
        $this->assertTrue($range1->overlaps($range2)); // 境界で接触
        $this->assertTrue($range2->overlaps($range1));
    }

    public function test_overlaps_境界で接する範囲_開区間(): void
    {
        // Arrange
        $range1 = LocalDateRange::open(
            LocalDate::of(2024, 1, 1),
            LocalDate::of(2024, 1, 15)
        );
        $range2 = LocalDateRange::open(
            LocalDate::of(2024, 1, 15),
            LocalDate::of(2024, 1, 31)
        );

        // Act & Assert
        $this->assertFalse($range1->overlaps($range2)); // 開区間では境界での接触は重なりとみなさない
        $this->assertFalse($range2->overlaps($range1));
    }

    public function test_days_閉区間の日数計算(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 5);
        $range = LocalDateRange::closed($from, $to);

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
        $range = LocalDateRange::open($from, $to);

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
        $daysLeft = LocalDateRange::halfOpenLeft($from, $to)->days();
        $daysRight = LocalDateRange::halfOpenRight($from, $to)->days();

        // Assert
        $this->assertSame(4, $daysLeft); // 1日を含まず、5日を含む = 4日間
        $this->assertSame(4, $daysRight); // 1日を含み、5日を含まない = 4日間
    }

    public function test_iterate_閉区間での日付の反復(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 3);
        $range = LocalDateRange::closed($from, $to);

        // Act
        $dates = [];
        foreach ($range->iterate() as $date) {
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
        $range = LocalDateRange::open($from, $to);

        // Act
        $dates = [];
        foreach ($range->iterate() as $date) {
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
        $range1 = LocalDateRange::closed($from, $to);
        $range2 = LocalDateRange::closed($from, $to);

        // Act & Assert
        $this->assertTrue($range1->equals($range2));
    }

    public function test_equals_異なる範囲(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);
        $range1 = LocalDateRange::closed($from, $to);
        $range2 = LocalDateRange::open($from, $to);

        // Act & Assert
        $this->assertFalse($range1->equals($range2)); // 範囲タイプが異なる
    }

    public function test_fromNullable_両方の値がnullでない場合(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);

        // Act
        $option = LocalDateRange::fromNullable($from, $to);

        // Assert
        $this->assertTrue($option->isSome());
        $range = $option->unwrap();
        $this->assertTrue($range->getFrom()->equals($from));
        $this->assertTrue($range->getTo()->equals($to));
    }

    public function test_fromNullable_いずれかの値がnullの場合(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);

        // Act
        $option1 = LocalDateRange::fromNullable(null, $from);
        $option2 = LocalDateRange::fromNullable($from, null);
        $option3 = LocalDateRange::fromNullable(null, null);

        // Assert
        $this->assertTrue($option1->isNone());
        $this->assertTrue($option2->isSome()); // fromがnullでなければ、toは自動的に最大日付になる
        $this->assertTrue($option3->isNone());
    }

    public function test_jsonSerialize(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);
        $range = LocalDateRange::closed($from, $to);

        // Act
        $json = $range->jsonSerialize();

        // Assert
        $this->assertSame('[2024-01-01, 2024-01-31]', $json);
    }

    public function test_from_デフォルトは開区間(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);
        $to = LocalDate::of(2024, 1, 31);

        // Act
        $range = LocalDateRange::from($from, $to);

        // Assert
        $this->assertSame(RangeType::CLOSED, $range->getRangeType());
        $this->assertSame('[2024-01-01, 2024-01-31]', $range->toISOString());
    }

    public function test_from_to引数省略時は最大日付になる(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);

        // Act
        $range = LocalDateRange::from($from);

        // Assert
        $this->assertSame($from, $range->getFrom());
        $this->assertSame('9999-12-31', $range->getTo()->toISOString());
        $this->assertSame(RangeType::CLOSED, $range->getRangeType());
    }

    public function test_tryFrom_to引数省略時も正常に動作(): void
    {
        // Arrange
        $from = LocalDate::of(2024, 1, 1);

        // Act
        $result = LocalDateRange::tryFrom($from);

        // Assert
        $this->assertTrue($result->isOk());
        $range = $result->unwrap();
        $this->assertSame($from, $range->getFrom());
        $this->assertSame('9999-12-31', $range->getTo()->toISOString());
    }
}
