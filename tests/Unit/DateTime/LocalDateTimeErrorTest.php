<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\DateTime;

use DateTimeZone;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use WizDevelop\PhpValueObject\DateTime\LocalDate;
use WizDevelop\PhpValueObject\DateTime\LocalDateTime;
use WizDevelop\PhpValueObject\DateTime\LocalTime;
use WizDevelop\PhpValueObject\Tests\TestCase;

/**
 * LocalDateTimeクラスの異常系テスト
 */
#[TestDox('LocalDateTimeクラスの異常系テスト')]
#[Group('DateTime')]
#[CoversClass(LocalDateTime::class)]
final class LocalDateTimeErrorTest extends TestCase
{
    // ------------------------------------------
    // 日付をまたぐ演算処理のテスト
    // ------------------------------------------

    #[Test]
    public function 日付境界での時間加算が正しく処理される(): void
    {
        // 23:59:59.999999から1マイクロ秒加算すると翌日の00:00:00.000000になる
        $dateTime = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(23, 59, 59, 999999)
        );

        $result = $dateTime->addMicros(1);

        $this->assertSame(2023, $result->getYear());
        $this->assertSame(5, $result->getMonth());
        $this->assertSame(16, $result->getDay());
        $this->assertSame(0, $result->getHour());
        $this->assertSame(0, $result->getMinute());
        $this->assertSame(0, $result->getSecond());
        $this->assertSame(0, $result->getMicro());
    }

    #[Test]
    public function 大きな時間加算で日付が正しく計算される(): void
    {
        $dateTime = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(12, 0, 0)
        );

        // 48時間（2日分）加算
        $result = $dateTime->addHours(48);

        $this->assertSame(2023, $result->getYear());
        $this->assertSame(5, $result->getMonth());
        $this->assertSame(17, $result->getDay());
        $this->assertSame(12, $result->getHour());
        $this->assertSame(0, $result->getMinute());
        $this->assertSame(0, $result->getSecond());
    }

    #[Test]
    public function 月をまたぐ日付加算が正しく処理される(): void
    {
        $dateTime = LocalDateTime::of(
            LocalDate::of(2023, 5, 31),
            LocalTime::of(12, 0, 0)
        );

        // 1日加算すると6月になる
        $result = $dateTime->addDays(1);

        $this->assertSame(2023, $result->getYear());
        $this->assertSame(6, $result->getMonth());
        $this->assertSame(1, $result->getDay());
        $this->assertSame(12, $result->getHour());
        $this->assertSame(0, $result->getMinute());
        $this->assertSame(0, $result->getSecond());
    }

    #[Test]
    public function 年をまたぐ日付加算が正しく処理される(): void
    {
        $dateTime = LocalDateTime::of(
            LocalDate::of(2023, 12, 31),
            LocalTime::of(23, 59, 59)
        );

        // 1秒加算すると翌年になる
        $result = $dateTime->addSeconds(1);

        $this->assertSame(2024, $result->getYear());
        $this->assertSame(1, $result->getMonth());
        $this->assertSame(1, $result->getDay());
        $this->assertSame(0, $result->getHour());
        $this->assertSame(0, $result->getMinute());
        $this->assertSame(0, $result->getSecond());
    }

    // ------------------------------------------
    // 日時の演算の組み合わせテスト
    // ------------------------------------------

    #[Test]
    public function 複数の演算を組み合わせた場合も正しく計算される(): void
    {
        $dateTime = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(12, 30, 45)
        );

        // 日付と時間の両方に対する演算
        $result = $dateTime
            ->addMonths(1)      // 6月15日
            ->addDays(20)       // 7月5日
            ->addHours(6)       // 18:30:45
            ->addMinutes(-45);  // 17:45:45

        $this->assertSame(2023, $result->getYear());
        $this->assertSame(7, $result->getMonth());
        $this->assertSame(5, $result->getDay());
        $this->assertSame(17, $result->getHour());
        $this->assertSame(45, $result->getMinute());
        $this->assertSame(45, $result->getSecond());
    }

    // ------------------------------------------
    // うるう年の日付処理のテスト
    // ------------------------------------------

    #[Test]
    public function うるう年の2月29日の処理が正しく行われる(): void
    {
        // うるう年の2月29日
        $dateTime = LocalDateTime::of(
            LocalDate::of(2024, 2, 29),
            LocalTime::of(12, 0, 0)
        );

        // 1年後は2月28日になる
        $result1 = $dateTime->addYears(1);
        $this->assertSame(2025, $result1->getYear());
        $this->assertSame(2, $result1->getMonth());
        $this->assertSame(28, $result1->getDay());

        // 4年後は2月29日になる
        $result2 = $dateTime->addYears(4);
        $this->assertSame(2028, $result2->getYear());
        $this->assertSame(2, $result2->getMonth());
        $this->assertSame(29, $result2->getDay());
    }

    // ------------------------------------------
    // 時間演算と負の値のテスト
    // ------------------------------------------

    #[Test]
    public function 負の時間加算が正しく処理される(): void
    {
        $dateTime = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(0, 0, 0)
        );

        // -1時間（前日の23時になる）
        $result1 = $dateTime->addHours(-1);
        $this->assertSame(2023, $result1->getYear());
        $this->assertSame(5, $result1->getMonth());
        $this->assertSame(14, $result1->getDay());
        $this->assertSame(23, $result1->getHour());
        $this->assertSame(0, $result1->getMinute());
        $this->assertSame(0, $result1->getSecond());

        // -1分（前日の23:59になる）
        $result2 = $dateTime->addMinutes(-1);
        $this->assertSame(2023, $result2->getYear());
        $this->assertSame(5, $result2->getMonth());
        $this->assertSame(14, $result2->getDay());
        $this->assertSame(23, $result2->getHour());
        $this->assertSame(59, $result2->getMinute());
        $this->assertSame(0, $result2->getSecond());
    }

    #[Test]
    public function 月の境界での日付加減算が正しく処理される(): void
    {
        // 月初
        $dateTime1 = LocalDateTime::of(
            LocalDate::of(2023, 5, 1),
            LocalTime::of(12, 0, 0)
        );

        // 1日減算すると前月末になる
        $result1 = $dateTime1->addDays(-1);
        $this->assertSame(2023, $result1->getYear());
        $this->assertSame(4, $result1->getMonth());
        $this->assertSame(30, $result1->getDay());

        // 月末
        $dateTime2 = LocalDateTime::of(
            LocalDate::of(2023, 2, 28),
            LocalTime::of(12, 0, 0)
        );

        // 1日加算すると翌月初になる
        $result2 = $dateTime2->addDays(1);
        $this->assertSame(2023, $result2->getYear());
        $this->assertSame(3, $result2->getMonth());
        $this->assertSame(1, $result2->getDay());
    }

    // ------------------------------------------
    // 日時比較メソッドのエッジケーステスト
    // ------------------------------------------

    #[Test]
    public function 日付は同じで時刻が異なる場合の比較が正しく処理される(): void
    {
        $dateTime1 = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(10, 0, 0)
        );

        $dateTime2 = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(11, 0, 0)
        );

        $this->assertTrue($dateTime1->isBefore($dateTime2));
        $this->assertFalse($dateTime2->isBefore($dateTime1));
        $this->assertFalse($dateTime1->isAfter($dateTime2));
        $this->assertTrue($dateTime2->isAfter($dateTime1));
    }

    #[Test]
    public function 時刻は同じで日付が異なる場合の比較が正しく処理される(): void
    {
        $dateTime1 = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(10, 0, 0)
        );

        $dateTime2 = LocalDateTime::of(
            LocalDate::of(2023, 5, 16),
            LocalTime::of(10, 0, 0)
        );

        $this->assertTrue($dateTime1->isBefore($dateTime2));
        $this->assertFalse($dateTime2->isBefore($dateTime1));
        $this->assertFalse($dateTime1->isAfter($dateTime2));
        $this->assertTrue($dateTime2->isAfter($dateTime1));
    }

    // ------------------------------------------
    // isFutureとisPastのテスト
    // ------------------------------------------

    #[Test]
    public function isFutureとisPastが現在時刻に基づいて正しく判定される(): void
    {
        // テスト実行時の現在日時
        $now = LocalDateTime::now(new DateTimeZone('UTC'));

        // 確実に過去の日時
        $past = $now->subDays(1);
        $this->assertTrue($past->isPast(new DateTimeZone('UTC')));
        $this->assertFalse($past->isFuture(new DateTimeZone('UTC')));

        // 確実に未来の日時
        $future = $now->addDays(1);
        $this->assertTrue($future->isFuture(new DateTimeZone('UTC')));
        $this->assertFalse($future->isPast(new DateTimeZone('UTC')));
    }

    // ------------------------------------------
    // addWithOverflowの内部動作テスト
    // ------------------------------------------

    #[Test]
    public function 複雑な時間加算の内部処理が正しく動作する(): void
    {
        // 時、分、秒、マイクロ秒が全て影響する加算
        $dateTime = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(23, 59, 59, 999999)
        );

        // 1時間、1分、1秒、1マイクロ秒を加算
        $result = $dateTime
            ->addHours(1)
            ->addMinutes(1)
            ->addSeconds(1)
            ->addMicros(1);

        $this->assertSame(2023, $result->getYear());
        $this->assertSame(5, $result->getMonth());
        $this->assertSame(16, $result->getDay());
        $this->assertSame(1, $result->getHour());
        $this->assertSame(1, $result->getMinute());
        $this->assertSame(1, $result->getSecond());
        $this->assertSame(0, $result->getMicro());
    }
}
