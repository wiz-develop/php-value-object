<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\DateTime;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\DateTime\LocalDateTime;
use WizDevelop\PhpValueObject\DateTime\LocalDateTimeRange;
use WizDevelop\PhpValueObject\DateTime\RangeType;
use WizDevelop\PhpValueObject\Error\ValueObjectError;

final class LocalDateTimeRangeTest extends TestCase
{
    public function test_閉区間で有効な範囲を作成できる(): void
    {
        // Arrange
        $from = LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00'));
        $to = LocalDateTime::from(new DateTimeImmutable('2024-01-31 18:00:00'));

        // Act
        $range = LocalDateTimeRange::closed($from, $to);

        // Assert
        $this->assertSame($from, $range->getFrom());
        $this->assertSame($to, $range->getTo());
        $this->assertSame(RangeType::CLOSED, $range->getRangeType());
        $this->assertSame('[2024-01-01T10:00, 2024-01-31T18:00]', $range->toISOString());
    }

    public function test_開区間で有効な範囲を作成できる(): void
    {
        // Arrange
        $from = LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00'));
        $to = LocalDateTime::from(new DateTimeImmutable('2024-01-31 18:00:00'));

        // Act
        $range = LocalDateTimeRange::open($from, $to);

        // Assert
        $this->assertSame(RangeType::OPEN, $range->getRangeType());
        $this->assertSame('(2024-01-01T10:00, 2024-01-31T18:00)', $range->toISOString());
    }

    public function test_半開区間で有効な範囲を作成できる(): void
    {
        // Arrange
        $from = LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00'));
        $to = LocalDateTime::from(new DateTimeImmutable('2024-01-31 18:00:00'));

        // Act
        $rangeLeft = LocalDateTimeRange::halfOpenLeft($from, $to);
        $rangeRight = LocalDateTimeRange::halfOpenRight($from, $to);

        // Assert
        $this->assertSame(RangeType::HALF_OPEN_LEFT, $rangeLeft->getRangeType());
        $this->assertSame('(2024-01-01T10:00, 2024-01-31T18:00]', $rangeLeft->toISOString());
        $this->assertSame(RangeType::HALF_OPEN_RIGHT, $rangeRight->getRangeType());
        $this->assertSame('[2024-01-01T10:00, 2024-01-31T18:00)', $rangeRight->toISOString());
    }

    public function test_開始日時が終了日時より後の場合エラーになる(): void
    {
        // Arrange
        $from = LocalDateTime::from(new DateTimeImmutable('2024-01-31 18:00:00'));
        $to = LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00'));

        // Act
        $result = LocalDateTimeRange::tryFrom($from, $to);

        // Assert
        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(ValueObjectError::class, $error);
        $this->assertSame('value_object.datetime_range.invalid_range', $error->getCode());
        $this->assertSame('開始日時は終了日時以前である必要があります', $error->getMessage());
    }

    public function test_contains_閉区間の境界値を含む(): void
    {
        // Arrange
        $from = LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00'));
        $to = LocalDateTime::from(new DateTimeImmutable('2024-01-31 18:00:00'));
        $range = LocalDateTimeRange::closed($from, $to);

        // Act & Assert
        $this->assertTrue($range->contains($from)); // 開始境界
        $this->assertTrue($range->contains($to)); // 終了境界
        $this->assertTrue($range->contains(LocalDateTime::from(new DateTimeImmutable('2024-01-15 12:00:00')))); // 中間
        $this->assertFalse($range->contains(LocalDateTime::from(new DateTimeImmutable('2023-12-31 23:59:59')))); // 範囲前
        $this->assertFalse($range->contains(LocalDateTime::from(new DateTimeImmutable('2024-02-01 00:00:00')))); // 範囲後
    }

    public function test_contains_開区間の境界値を含まない(): void
    {
        // Arrange
        $from = LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00'));
        $to = LocalDateTime::from(new DateTimeImmutable('2024-01-31 18:00:00'));
        $range = LocalDateTimeRange::open($from, $to);

        // Act & Assert
        $this->assertFalse($range->contains($from)); // 開始境界
        $this->assertFalse($range->contains($to)); // 終了境界
        $this->assertTrue($range->contains(LocalDateTime::from(new DateTimeImmutable('2024-01-15 12:00:00')))); // 中間
    }

    public function test_contains_半開区間の境界値(): void
    {
        // Arrange
        $from = LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00'));
        $to = LocalDateTime::from(new DateTimeImmutable('2024-01-31 18:00:00'));

        // Act & Assert
        // 左開区間
        $rangeLeft = LocalDateTimeRange::halfOpenLeft($from, $to);
        $this->assertFalse($rangeLeft->contains($from)); // 開始境界（含まない）
        $this->assertTrue($rangeLeft->contains($to)); // 終了境界（含む）

        // 右開区間
        $rangeRight = LocalDateTimeRange::halfOpenRight($from, $to);
        $this->assertTrue($rangeRight->contains($from)); // 開始境界（含む）
        $this->assertFalse($rangeRight->contains($to)); // 終了境界（含まない）
    }

    public function test_overlaps_重なりがある範囲(): void
    {
        // Arrange
        $range1 = LocalDateTimeRange::closed(
            LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00')),
            LocalDateTime::from(new DateTimeImmutable('2024-01-15 18:00:00'))
        );
        $range2 = LocalDateTimeRange::closed(
            LocalDateTime::from(new DateTimeImmutable('2024-01-10 10:00:00')),
            LocalDateTime::from(new DateTimeImmutable('2024-01-20 18:00:00'))
        );

        // Act & Assert
        $this->assertTrue($range1->overlaps($range2));
        $this->assertTrue($range2->overlaps($range1));
    }

    public function test_overlaps_重なりがない範囲(): void
    {
        // Arrange
        $range1 = LocalDateTimeRange::closed(
            LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00')),
            LocalDateTime::from(new DateTimeImmutable('2024-01-10 18:00:00'))
        );
        $range2 = LocalDateTimeRange::closed(
            LocalDateTime::from(new DateTimeImmutable('2024-01-20 10:00:00')),
            LocalDateTime::from(new DateTimeImmutable('2024-01-31 18:00:00'))
        );

        // Act & Assert
        $this->assertFalse($range1->overlaps($range2));
        $this->assertFalse($range2->overlaps($range1));
    }

    public function test_overlaps_境界で接する範囲_閉区間(): void
    {
        // Arrange
        $range1 = LocalDateTimeRange::closed(
            LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00')),
            LocalDateTime::from(new DateTimeImmutable('2024-01-15 18:00:00'))
        );
        $range2 = LocalDateTimeRange::closed(
            LocalDateTime::from(new DateTimeImmutable('2024-01-15 18:00:00')),
            LocalDateTime::from(new DateTimeImmutable('2024-01-31 22:00:00'))
        );

        // Act & Assert
        $this->assertTrue($range1->overlaps($range2)); // 境界で接触
        $this->assertTrue($range2->overlaps($range1));
    }

    public function test_overlaps_境界で接する範囲_開区間(): void
    {
        // Arrange
        $range1 = LocalDateTimeRange::open(
            LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00')),
            LocalDateTime::from(new DateTimeImmutable('2024-01-15 18:00:00'))
        );
        $range2 = LocalDateTimeRange::open(
            LocalDateTime::from(new DateTimeImmutable('2024-01-15 18:00:00')),
            LocalDateTime::from(new DateTimeImmutable('2024-01-31 22:00:00'))
        );

        // Act & Assert
        $this->assertFalse($range1->overlaps($range2)); // 開区間では境界での接触は重なりとみなさない
        $this->assertFalse($range2->overlaps($range1));
    }

    public function test_duration_計算(): void
    {
        // Arrange
        $from = LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00'));
        $to = LocalDateTime::from(new DateTimeImmutable('2024-01-02 10:00:00'));
        $range = LocalDateTimeRange::closed($from, $to);

        // Act & Assert
        $this->assertSame(86400, $range->durationInSeconds()); // 24時間
        $this->assertSame(1440.0, $range->durationInMinutes()); // 24時間 * 60分
        $this->assertSame(24.0, $range->durationInHours()); // 24時間
        $this->assertSame(1.0, $range->durationInDays()); // 1日
    }

    public function test_equals_同じ範囲(): void
    {
        // Arrange
        $from = LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00'));
        $to = LocalDateTime::from(new DateTimeImmutable('2024-01-31 18:00:00'));
        $range1 = LocalDateTimeRange::closed($from, $to);
        $range2 = LocalDateTimeRange::closed($from, $to);

        // Act & Assert
        $this->assertTrue($range1->equals($range2));
    }

    public function test_equals_異なる範囲(): void
    {
        // Arrange
        $from = LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00'));
        $to = LocalDateTime::from(new DateTimeImmutable('2024-01-31 18:00:00'));
        $range1 = LocalDateTimeRange::closed($from, $to);
        $range2 = LocalDateTimeRange::open($from, $to);

        // Act & Assert
        $this->assertFalse($range1->equals($range2)); // 範囲タイプが異なる
    }

    public function test_fromNullable_両方の値がnullでない場合(): void
    {
        // Arrange
        $from = LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00'));
        $to = LocalDateTime::from(new DateTimeImmutable('2024-01-31 18:00:00'));

        // Act
        $option = LocalDateTimeRange::fromNullable($from, $to);

        // Assert
        $this->assertTrue($option->isSome());
        $range = $option->unwrap();
        $this->assertTrue($range->getFrom()->equals($from));
        $this->assertTrue($range->getTo()->equals($to));
    }

    public function test_fromNullable_いずれかの値がnullの場合(): void
    {
        // Arrange
        $from = LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00'));

        // Act
        $option1 = LocalDateTimeRange::fromNullable(null, $from);
        $option2 = LocalDateTimeRange::fromNullable($from, null);
        $option3 = LocalDateTimeRange::fromNullable(null, null);

        // Assert
        $this->assertTrue($option1->isNone());
        $this->assertTrue($option2->isSome()); // fromがnullでなければ、toは自動的に最大日時になる
        $this->assertTrue($option3->isNone());
    }

    public function test_jsonSerialize(): void
    {
        // Arrange
        $from = LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00'));
        $to = LocalDateTime::from(new DateTimeImmutable('2024-01-31 18:00:00'));
        $range = LocalDateTimeRange::closed($from, $to);

        // Act
        $json = $range->jsonSerialize();

        // Assert
        $this->assertSame('[2024-01-01T10:00, 2024-01-31T18:00]', $json);
    }

    public function test_from_デフォルトは閉区間(): void
    {
        // Arrange
        $from = LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00'));
        $to = LocalDateTime::from(new DateTimeImmutable('2024-01-31 18:00:00'));

        // Act
        $range = LocalDateTimeRange::from($from, $to);

        // Assert
        $this->assertSame(RangeType::CLOSED, $range->getRangeType());
        $this->assertSame('[2024-01-01T10:00, 2024-01-31T18:00]', $range->toISOString());
    }

    public function test_from_to引数省略時は最大日時になる(): void
    {
        // Arrange
        $from = LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00'));

        // Act
        $range = LocalDateTimeRange::from($from);

        // Assert
        $this->assertSame($from, $range->getFrom());
        $this->assertSame('9999-12-31T23:59:59', $range->getTo()->toISOString());
        $this->assertSame(RangeType::CLOSED, $range->getRangeType());
    }

    public function test_tryFrom_to引数省略時も正常に動作(): void
    {
        // Arrange
        $from = LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00'));

        // Act
        $result = LocalDateTimeRange::tryFrom($from);

        // Assert
        $this->assertTrue($result->isOk());
        $range = $result->unwrap();
        $this->assertSame($from, $range->getFrom());
        $this->assertSame('9999-12-31T23:59:59', $range->getTo()->toISOString());
    }
}
