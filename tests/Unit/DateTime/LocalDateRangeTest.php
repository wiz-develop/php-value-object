<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\DateTime;

use AssertionError;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\DateTime\LocalDate;
use WizDevelop\PhpValueObject\DateTime\LocalDateRange;
use WizDevelop\PhpValueObject\DateTime\RangeType;
use WizDevelop\PhpValueObject\Error\ValueObjectError;

/**
 * @phpstan-import-type Year from LocalDate
 * @phpstan-import-type Month from LocalDate
 * @phpstan-import-type Day from LocalDate
 *
 * @phpstan-type RangeData array{from: array{Year, Month, Day}, to: array{Year, Month, Day}, type: RangeType}
 *
 * @phpstan-type OverlapsTestCase array{
 *     range1Data: RangeData,
 *     range2Data: RangeData,
 *     expectedOverlap: bool,
 *     description: string
 * }
 */
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

    /**
     * overlapsメソッドの包括的なテストケース（DataProvider使用）
     * RangeTypeの全組み合わせ（4×4 = 16パターン）と範囲の位置関係を網羅
     *
     * @dataProvider provideOverlaps_comprehensiveCases
     * @param RangeData $range1Data
     * @param RangeData $range2Data
     */
    public function test_overlaps_comprehensive(
        array $range1Data,
        array $range2Data,
        bool $expectedOverlap,
        string $description
    ): void {
        // Arrange
        $range1 = LocalDateRange::from(
            LocalDate::of($range1Data['from'][0], $range1Data['from'][1], $range1Data['from'][2]),
            LocalDate::of($range1Data['to'][0], $range1Data['to'][1], $range1Data['to'][2]),
            $range1Data['type']
        );
        $range2 = LocalDateRange::from(
            LocalDate::of($range2Data['from'][0], $range2Data['from'][1], $range2Data['from'][2]),
            LocalDate::of($range2Data['to'][0], $range2Data['to'][1], $range2Data['to'][2]),
            $range2Data['type']
        );

        // Act & Assert
        $this->assertSame($expectedOverlap, $range1->overlaps($range2), $description . ' (range1->overlaps(range2))');
        $this->assertSame($expectedOverlap, $range2->overlaps($range1), $description . ' (range2->overlaps(range1))');
    }

    /**
     * overlapsメソッドのテストデータプロバイダー
     *
     * @return array<string,OverlapsTestCase>
     */
    public static function provideOverlaps_comprehensiveCases(): iterable
    {
        return [
            // =========================================================================
            // 1. 完全に離れている範囲のテストケース（全RangeType組み合わせ）
            // =========================================================================
            'separated_CLOSED_CLOSED' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 10], 'type' => RangeType::CLOSED],
                'range2Data' => ['from' => [2024, 1, 20], 'to' => [2024, 1, 30], 'type' => RangeType::CLOSED],
                'expectedOverlap' => false,
                'description' => '完全に離れている範囲 (CLOSED vs CLOSED)',
            ],
            'separated_CLOSED_OPEN' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 10], 'type' => RangeType::CLOSED],
                'range2Data' => ['from' => [2024, 1, 20], 'to' => [2024, 1, 30], 'type' => RangeType::OPEN],
                'expectedOverlap' => false,
                'description' => '完全に離れている範囲 (CLOSED vs OPEN)',
            ],
            'separated_CLOSED_HALF_OPEN_LEFT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 10], 'type' => RangeType::CLOSED],
                'range2Data' => ['from' => [2024, 1, 20], 'to' => [2024, 1, 30], 'type' => RangeType::HALF_OPEN_LEFT],
                'expectedOverlap' => false,
                'description' => '完全に離れている範囲 (CLOSED vs HALF_OPEN_LEFT)',
            ],
            'separated_CLOSED_HALF_OPEN_RIGHT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 10], 'type' => RangeType::CLOSED],
                'range2Data' => ['from' => [2024, 1, 20], 'to' => [2024, 1, 30], 'type' => RangeType::HALF_OPEN_RIGHT],
                'expectedOverlap' => false,
                'description' => '完全に離れている範囲 (CLOSED vs HALF_OPEN_RIGHT)',
            ],
            'separated_OPEN_CLOSED' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 10], 'type' => RangeType::OPEN],
                'range2Data' => ['from' => [2024, 1, 20], 'to' => [2024, 1, 30], 'type' => RangeType::CLOSED],
                'expectedOverlap' => false,
                'description' => '完全に離れている範囲 (OPEN vs CLOSED)',
            ],
            'separated_OPEN_OPEN' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 10], 'type' => RangeType::OPEN],
                'range2Data' => ['from' => [2024, 1, 20], 'to' => [2024, 1, 30], 'type' => RangeType::OPEN],
                'expectedOverlap' => false,
                'description' => '完全に離れている範囲 (OPEN vs OPEN)',
            ],
            'separated_OPEN_HALF_OPEN_LEFT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 10], 'type' => RangeType::OPEN],
                'range2Data' => ['from' => [2024, 1, 20], 'to' => [2024, 1, 30], 'type' => RangeType::HALF_OPEN_LEFT],
                'expectedOverlap' => false,
                'description' => '完全に離れている範囲 (OPEN vs HALF_OPEN_LEFT)',
            ],
            'separated_OPEN_HALF_OPEN_RIGHT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 10], 'type' => RangeType::OPEN],
                'range2Data' => ['from' => [2024, 1, 20], 'to' => [2024, 1, 30], 'type' => RangeType::HALF_OPEN_RIGHT],
                'expectedOverlap' => false,
                'description' => '完全に離れている範囲 (OPEN vs HALF_OPEN_RIGHT)',
            ],
            'separated_HALF_OPEN_LEFT_CLOSED' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 10], 'type' => RangeType::HALF_OPEN_LEFT],
                'range2Data' => ['from' => [2024, 1, 20], 'to' => [2024, 1, 30], 'type' => RangeType::CLOSED],
                'expectedOverlap' => false,
                'description' => '完全に離れている範囲 (HALF_OPEN_LEFT vs CLOSED)',
            ],
            'separated_HALF_OPEN_LEFT_OPEN' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 10], 'type' => RangeType::HALF_OPEN_LEFT],
                'range2Data' => ['from' => [2024, 1, 20], 'to' => [2024, 1, 30], 'type' => RangeType::OPEN],
                'expectedOverlap' => false,
                'description' => '完全に離れている範囲 (HALF_OPEN_LEFT vs OPEN)',
            ],
            'separated_HALF_OPEN_LEFT_HALF_OPEN_LEFT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 10], 'type' => RangeType::HALF_OPEN_LEFT],
                'range2Data' => ['from' => [2024, 1, 20], 'to' => [2024, 1, 30], 'type' => RangeType::HALF_OPEN_LEFT],
                'expectedOverlap' => false,
                'description' => '完全に離れている範囲 (HALF_OPEN_LEFT vs HALF_OPEN_LEFT)',
            ],
            'separated_HALF_OPEN_LEFT_HALF_OPEN_RIGHT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 10], 'type' => RangeType::HALF_OPEN_LEFT],
                'range2Data' => ['from' => [2024, 1, 20], 'to' => [2024, 1, 30], 'type' => RangeType::HALF_OPEN_RIGHT],
                'expectedOverlap' => false,
                'description' => '完全に離れている範囲 (HALF_OPEN_LEFT vs HALF_OPEN_RIGHT)',
            ],
            'separated_HALF_OPEN_RIGHT_CLOSED' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 10], 'type' => RangeType::HALF_OPEN_RIGHT],
                'range2Data' => ['from' => [2024, 1, 20], 'to' => [2024, 1, 30], 'type' => RangeType::CLOSED],
                'expectedOverlap' => false,
                'description' => '完全に離れている範囲 (HALF_OPEN_RIGHT vs CLOSED)',
            ],
            'separated_HALF_OPEN_RIGHT_OPEN' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 10], 'type' => RangeType::HALF_OPEN_RIGHT],
                'range2Data' => ['from' => [2024, 1, 20], 'to' => [2024, 1, 30], 'type' => RangeType::OPEN],
                'expectedOverlap' => false,
                'description' => '完全に離れている範囲 (HALF_OPEN_RIGHT vs OPEN)',
            ],
            'separated_HALF_OPEN_RIGHT_HALF_OPEN_LEFT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 10], 'type' => RangeType::HALF_OPEN_RIGHT],
                'range2Data' => ['from' => [2024, 1, 20], 'to' => [2024, 1, 30], 'type' => RangeType::HALF_OPEN_LEFT],
                'expectedOverlap' => false,
                'description' => '完全に離れている範囲 (HALF_OPEN_RIGHT vs HALF_OPEN_LEFT)',
            ],
            'separated_HALF_OPEN_RIGHT_HALF_OPEN_RIGHT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 10], 'type' => RangeType::HALF_OPEN_RIGHT],
                'range2Data' => ['from' => [2024, 1, 20], 'to' => [2024, 1, 30], 'type' => RangeType::HALF_OPEN_RIGHT],
                'expectedOverlap' => false,
                'description' => '完全に離れている範囲 (HALF_OPEN_RIGHT vs HALF_OPEN_RIGHT)',
            ],
            // =========================================================================
            // 2. 境界で接触する範囲のテストケース（全RangeType組み合わせ）
            // =========================================================================
            'touching_CLOSED_CLOSED' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::CLOSED],
                'range2Data' => ['from' => [2024, 1, 15], 'to' => [2024, 1, 30], 'type' => RangeType::CLOSED],
                'expectedOverlap' => true,
                'description' => '境界で接触（両方の境界値を含む） (CLOSED vs CLOSED)',
            ],
            'touching_CLOSED_OPEN' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::CLOSED],
                'range2Data' => ['from' => [2024, 1, 15], 'to' => [2024, 1, 30], 'type' => RangeType::OPEN],
                'expectedOverlap' => false,
                'description' => '境界で接触（第1範囲は境界値を含み、第2範囲は含まない） (CLOSED vs OPEN)',
            ],
            'touching_CLOSED_HALF_OPEN_LEFT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::CLOSED],
                'range2Data' => ['from' => [2024, 1, 15], 'to' => [2024, 1, 30], 'type' => RangeType::HALF_OPEN_LEFT],
                'expectedOverlap' => false,
                'description' => '境界で接触（第1範囲は境界値を含み、第2範囲は含まない） (CLOSED vs HALF_OPEN_LEFT)',
            ],
            'touching_CLOSED_HALF_OPEN_RIGHT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::CLOSED],
                'range2Data' => ['from' => [2024, 1, 15], 'to' => [2024, 1, 30], 'type' => RangeType::HALF_OPEN_RIGHT],
                'expectedOverlap' => true,
                'description' => '境界で接触（両方の範囲が境界値を含む） (CLOSED vs HALF_OPEN_RIGHT)',
            ],
            'touching_OPEN_CLOSED' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::OPEN],
                'range2Data' => ['from' => [2024, 1, 15], 'to' => [2024, 1, 30], 'type' => RangeType::CLOSED],
                'expectedOverlap' => false,
                'description' => '境界で接触（第1範囲は境界値を含まず、第2範囲は含む） (OPEN vs CLOSED)',
            ],
            'touching_OPEN_OPEN' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::OPEN],
                'range2Data' => ['from' => [2024, 1, 15], 'to' => [2024, 1, 30], 'type' => RangeType::OPEN],
                'expectedOverlap' => false,
                'description' => '境界で接触（両方とも境界値を含まない） (OPEN vs OPEN)',
            ],
            'touching_OPEN_HALF_OPEN_LEFT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::OPEN],
                'range2Data' => ['from' => [2024, 1, 15], 'to' => [2024, 1, 30], 'type' => RangeType::HALF_OPEN_LEFT],
                'expectedOverlap' => false,
                'description' => '境界で接触（両方とも境界値を含まない） (OPEN vs HALF_OPEN_LEFT)',
            ],
            'touching_OPEN_HALF_OPEN_RIGHT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::OPEN],
                'range2Data' => ['from' => [2024, 1, 15], 'to' => [2024, 1, 30], 'type' => RangeType::HALF_OPEN_RIGHT],
                'expectedOverlap' => false,
                'description' => '境界で接触（第1範囲は境界値を含まず、第2範囲は含む） (OPEN vs HALF_OPEN_RIGHT)',
            ],
            'touching_HALF_OPEN_LEFT_CLOSED' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::HALF_OPEN_LEFT],
                'range2Data' => ['from' => [2024, 1, 15], 'to' => [2024, 1, 30], 'type' => RangeType::CLOSED],
                'expectedOverlap' => true,
                'description' => '境界で接触（両方の範囲が境界値を含む） (HALF_OPEN_LEFT vs CLOSED)',
            ],
            'touching_HALF_OPEN_LEFT_OPEN' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::HALF_OPEN_LEFT],
                'range2Data' => ['from' => [2024, 1, 15], 'to' => [2024, 1, 30], 'type' => RangeType::OPEN],
                'expectedOverlap' => false,
                'description' => '境界で接触（第1範囲は境界値を含み、第2範囲は含まない） (HALF_OPEN_LEFT vs OPEN)',
            ],
            'touching_HALF_OPEN_LEFT_HALF_OPEN_LEFT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::HALF_OPEN_LEFT],
                'range2Data' => ['from' => [2024, 1, 15], 'to' => [2024, 1, 30], 'type' => RangeType::HALF_OPEN_LEFT],
                'expectedOverlap' => false,
                'description' => '境界で接触（第1範囲は境界値を含み、第2範囲は含まない） (HALF_OPEN_LEFT vs HALF_OPEN_LEFT)',
            ],
            'touching_HALF_OPEN_LEFT_HALF_OPEN_RIGHT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::HALF_OPEN_LEFT],
                'range2Data' => ['from' => [2024, 1, 15], 'to' => [2024, 1, 30], 'type' => RangeType::HALF_OPEN_RIGHT],
                'expectedOverlap' => true,
                'description' => '境界で接触（両方の範囲が境界値を含む） (HALF_OPEN_LEFT vs HALF_OPEN_RIGHT)',
            ],
            'touching_HALF_OPEN_RIGHT_CLOSED' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::HALF_OPEN_RIGHT],
                'range2Data' => ['from' => [2024, 1, 15], 'to' => [2024, 1, 30], 'type' => RangeType::CLOSED],
                'expectedOverlap' => false,
                'description' => '境界で接触（第1範囲は境界値を含まず、第2範囲は含む） (HALF_OPEN_RIGHT vs CLOSED)',
            ],
            'touching_HALF_OPEN_RIGHT_OPEN' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::HALF_OPEN_RIGHT],
                'range2Data' => ['from' => [2024, 1, 15], 'to' => [2024, 1, 30], 'type' => RangeType::OPEN],
                'expectedOverlap' => false,
                'description' => '境界で接触（両方とも境界値を含まない） (HALF_OPEN_RIGHT vs OPEN)',
            ],
            'touching_HALF_OPEN_RIGHT_HALF_OPEN_LEFT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::HALF_OPEN_RIGHT],
                'range2Data' => ['from' => [2024, 1, 15], 'to' => [2024, 1, 30], 'type' => RangeType::HALF_OPEN_LEFT],
                'expectedOverlap' => false,
                'description' => '境界で接触（両方とも境界値を含まない） (HALF_OPEN_RIGHT vs HALF_OPEN_LEFT)',
            ],
            'touching_HALF_OPEN_RIGHT_HALF_OPEN_RIGHT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::HALF_OPEN_RIGHT],
                'range2Data' => ['from' => [2024, 1, 15], 'to' => [2024, 1, 30], 'type' => RangeType::HALF_OPEN_RIGHT],
                'expectedOverlap' => false,
                'description' => '境界で接触（第1範囲は境界値を含まず、第2範囲は含む） (HALF_OPEN_RIGHT vs HALF_OPEN_RIGHT)',
            ],
            // =========================================================================
            // 3. 重なっている範囲のテストケース（代表的なRangeType組み合わせ）
            // =========================================================================
            'overlapping_CLOSED_CLOSED' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::CLOSED],
                'range2Data' => ['from' => [2024, 1, 10], 'to' => [2024, 1, 20], 'type' => RangeType::CLOSED],
                'expectedOverlap' => true,
                'description' => '部分的に重なっている範囲 (CLOSED vs CLOSED)',
            ],
            'overlapping_OPEN_OPEN' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::OPEN],
                'range2Data' => ['from' => [2024, 1, 10], 'to' => [2024, 1, 20], 'type' => RangeType::OPEN],
                'expectedOverlap' => true,
                'description' => '部分的に重なっている範囲 (OPEN vs OPEN)',
            ],
            'overlapping_HALF_OPEN_LEFT_HALF_OPEN_RIGHT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::HALF_OPEN_LEFT],
                'range2Data' => ['from' => [2024, 1, 10], 'to' => [2024, 1, 20], 'type' => RangeType::HALF_OPEN_RIGHT],
                'expectedOverlap' => true,
                'description' => '部分的に重なっている範囲 (HALF_OPEN_LEFT vs HALF_OPEN_RIGHT)',
            ],
            'overlapping_HALF_OPEN_RIGHT_HALF_OPEN_LEFT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 15], 'type' => RangeType::HALF_OPEN_RIGHT],
                'range2Data' => ['from' => [2024, 1, 10], 'to' => [2024, 1, 20], 'type' => RangeType::HALF_OPEN_LEFT],
                'expectedOverlap' => true,
                'description' => '部分的に重なっている範囲 (HALF_OPEN_RIGHT vs HALF_OPEN_LEFT)',
            ],
            // =========================================================================
            // 4. 一方が他方を完全に含む範囲のテストケース（代表的なRangeType組み合わせ）
            // =========================================================================
            'contains_CLOSED_CLOSED' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 31], 'type' => RangeType::CLOSED],
                'range2Data' => ['from' => [2024, 1, 10], 'to' => [2024, 1, 20], 'type' => RangeType::CLOSED],
                'expectedOverlap' => true,
                'description' => '第1範囲が第2範囲を完全に含む (CLOSED vs CLOSED)',
            ],
            'contains_OPEN_OPEN' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 31], 'type' => RangeType::OPEN],
                'range2Data' => ['from' => [2024, 1, 10], 'to' => [2024, 1, 20], 'type' => RangeType::OPEN],
                'expectedOverlap' => true,
                'description' => '第1範囲が第2範囲を完全に含む (OPEN vs OPEN)',
            ],
            'contains_CLOSED_OPEN' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 31], 'type' => RangeType::CLOSED],
                'range2Data' => ['from' => [2024, 1, 10], 'to' => [2024, 1, 20], 'type' => RangeType::OPEN],
                'expectedOverlap' => true,
                'description' => '第1範囲（閉区間）が第2範囲（開区間）を完全に含む (CLOSED vs OPEN)',
            ],
            'contains_OPEN_CLOSED' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 31], 'type' => RangeType::OPEN],
                'range2Data' => ['from' => [2024, 1, 10], 'to' => [2024, 1, 20], 'type' => RangeType::CLOSED],
                'expectedOverlap' => true,
                'description' => '第1範囲（開区間）が第2範囲（閉区間）を完全に含む (OPEN vs CLOSED)',
            ],
            // =========================================================================
            // 5. 同一範囲のテストケース（全RangeType組み合わせ）
            // =========================================================================
            'identical_CLOSED_CLOSED' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 31], 'type' => RangeType::CLOSED],
                'range2Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 31], 'type' => RangeType::CLOSED],
                'expectedOverlap' => true,
                'description' => '同一範囲 (CLOSED vs CLOSED)',
            ],
            'identical_OPEN_OPEN' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 31], 'type' => RangeType::OPEN],
                'range2Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 31], 'type' => RangeType::OPEN],
                'expectedOverlap' => true,
                'description' => '同一範囲 (OPEN vs OPEN)',
            ],
            'identical_HALF_OPEN_LEFT_HALF_OPEN_LEFT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 31], 'type' => RangeType::HALF_OPEN_LEFT],
                'range2Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 31], 'type' => RangeType::HALF_OPEN_LEFT],
                'expectedOverlap' => true,
                'description' => '同一範囲 (HALF_OPEN_LEFT vs HALF_OPEN_LEFT)',
            ],
            'identical_HALF_OPEN_RIGHT_HALF_OPEN_RIGHT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 31], 'type' => RangeType::HALF_OPEN_RIGHT],
                'range2Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 31], 'type' => RangeType::HALF_OPEN_RIGHT],
                'expectedOverlap' => true,
                'description' => '同一範囲 (HALF_OPEN_RIGHT vs HALF_OPEN_RIGHT)',
            ],
            'identical_mixed_CLOSED_OPEN' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 31], 'type' => RangeType::CLOSED],
                'range2Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 31], 'type' => RangeType::OPEN],
                'expectedOverlap' => true,
                'description' => '同じ期間だが異なる区間タイプ (CLOSED vs OPEN)',
            ],
            'identical_mixed_HALF_OPEN_LEFT_HALF_OPEN_RIGHT' => [
                'range1Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 31], 'type' => RangeType::HALF_OPEN_LEFT],
                'range2Data' => ['from' => [2024, 1, 1], 'to' => [2024, 1, 31], 'type' => RangeType::HALF_OPEN_RIGHT],
                'expectedOverlap' => true,
                'description' => '同じ期間だが異なる区間タイプ (HALF_OPEN_LEFT vs HALF_OPEN_RIGHT)',
            ],
        ];
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
        $this->expectException(AssertionError::class);
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
        $this->expectException(AssertionError::class);
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
