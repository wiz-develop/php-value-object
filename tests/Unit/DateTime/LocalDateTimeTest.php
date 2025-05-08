<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\DateTime;

use DateTimeImmutable;
use DateTimeZone;
use Error;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use ReflectionClass;
use WizDevelop\PhpValueObject\DateTime\LocalDate;
use WizDevelop\PhpValueObject\DateTime\LocalDateTime;
use WizDevelop\PhpValueObject\DateTime\LocalTime;
use WizDevelop\PhpValueObject\Tests\TestCase;

/**
 * LocalDateTimeクラスのテスト
 */
#[TestDox('LocalDateTimeクラスのテスト')]
#[Group('DateTime')]
#[CoversClass(LocalDateTime::class)]
final class LocalDateTimeTest extends TestCase
{
    // ------------------------------------------
    // インスタンス生成と基本機能のテスト
    // ------------------------------------------

    #[Test]
    public function 有効な値でインスタンスが作成できる(): void
    {
        $date = LocalDate::of(2023, 5, 15);
        $time = LocalTime::of(10, 30, 45);

        $dateTime = LocalDateTime::of($date, $time);

        $this->assertSame(2023, $dateTime->getYear());
        $this->assertSame(5, $dateTime->getMonth());
        $this->assertSame(15, $dateTime->getDay());
        $this->assertSame(10, $dateTime->getHour());
        $this->assertSame(30, $dateTime->getMinute());
        $this->assertSame(45, $dateTime->getSecond());
        $this->assertSame(0, $dateTime->getMicro());
    }

    #[Test]
    public function DateTimeからインスタンスが作成できる(): void
    {
        $nativeDateTime = new DateTimeImmutable('2023-05-15 10:30:45.123456', new DateTimeZone('UTC'));
        $dateTime = LocalDateTime::from($nativeDateTime);

        $this->assertSame(2023, $dateTime->getYear());
        $this->assertSame(5, $dateTime->getMonth());
        $this->assertSame(15, $dateTime->getDay());
        $this->assertSame(10, $dateTime->getHour());
        $this->assertSame(30, $dateTime->getMinute());
        $this->assertSame(45, $dateTime->getSecond());
        $this->assertSame(123456, $dateTime->getMicro());
    }

    #[Test]
    public function fromNullableでNullを扱える(): void
    {
        $option1 = LocalDateTime::fromNullable(null);
        $this->assertTrue($option1->isNone());

        $nativeDateTime = new DateTimeImmutable('2023-05-15 10:30:45');
        $option2 = LocalDateTime::fromNullable($nativeDateTime);
        $this->assertTrue($option2->isSome());

        $dateTime = $option2->unwrap();
        $this->assertSame(2023, $dateTime->getYear());
        $this->assertSame(5, $dateTime->getMonth());
        $this->assertSame(15, $dateTime->getDay());
        $this->assertSame(10, $dateTime->getHour());
        $this->assertSame(30, $dateTime->getMinute());
        $this->assertSame(45, $dateTime->getSecond());
    }

    #[Test]
    public function tryFromNullableでNullを扱える(): void
    {
        $result1 = LocalDateTime::tryFromNullable(null);
        $this->assertTrue($result1->isOk());
        $this->assertTrue($result1->unwrap()->isNone());

        $nativeDateTime = new DateTimeImmutable('2023-05-15 10:30:45');
        $result2 = LocalDateTime::tryFromNullable($nativeDateTime);
        $this->assertTrue($result2->isOk());
        $this->assertTrue($result2->unwrap()->isSome());

        $dateTime = $result2->unwrap()->unwrap();
        $this->assertSame(2023, $dateTime->getYear());
        $this->assertSame(5, $dateTime->getMonth());
        $this->assertSame(15, $dateTime->getDay());
        $this->assertSame(10, $dateTime->getHour());
        $this->assertSame(30, $dateTime->getMinute());
        $this->assertSame(45, $dateTime->getSecond());
    }

    #[Test]
    public function nowで現在日時のインスタンスが作成できる(): void
    {
        $dateTime = LocalDateTime::now(new DateTimeZone('UTC'));

        // 現在日時なので厳密な値のテストはできないが、今日の日付と一致するはず
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $this->assertSame((int)$now->format('Y'), $dateTime->getYear());
        $this->assertSame((int)$now->format('n'), $dateTime->getMonth());
        $this->assertSame((int)$now->format('j'), $dateTime->getDay());
        $this->assertSame((int)$now->format('G'), $dateTime->getHour());
    }

    // ------------------------------------------
    // getterメソッドのテスト
    // ------------------------------------------

    #[Test]
    public function getterメソッドで各値が取得できる(): void
    {
        $date = LocalDate::of(2023, 5, 15);
        $time = LocalTime::of(10, 30, 45, 123456);

        $dateTime = LocalDateTime::of($date, $time);

        $this->assertSame(2023, $dateTime->getYear());
        $this->assertSame(5, $dateTime->getMonth());
        $this->assertSame(15, $dateTime->getDay());
        $this->assertSame(10, $dateTime->getHour());
        $this->assertSame(30, $dateTime->getMinute());
        $this->assertSame(45, $dateTime->getSecond());
        $this->assertSame(123456, $dateTime->getMicro());

        $this->assertSame($date, $dateTime->getDate());
        $this->assertSame($time, $dateTime->getTime());
    }

    // ------------------------------------------
    // 変換メソッドのテスト
    // ------------------------------------------

    #[Test]
    public function toISOStringでISO形式の文字列が取得できる(): void
    {
        $date = LocalDate::of(2023, 5, 15);
        $time = LocalTime::of(10, 30, 45);

        $dateTime = LocalDateTime::of($date, $time);

        $this->assertSame('2023-05-15T10:30:45', $dateTime->toISOString());

        // マイクロ秒を含む場合
        $time2 = LocalTime::of(10, 30, 45, 123456);
        $dateTime2 = LocalDateTime::of($date, $time2);

        $this->assertSame('2023-05-15T10:30:45.123456', $dateTime2->toISOString());
    }

    #[Test]
    public function toDateTimeImmutableでDateTimeImmutableに変換できる(): void
    {
        $date = LocalDate::of(2023, 5, 15);
        $time = LocalTime::of(10, 30, 45, 123456);

        $dateTime = LocalDateTime::of($date, $time);
        $nativeDateTime = $dateTime->toDateTimeImmutable();

        $this->assertInstanceOf(DateTimeImmutable::class, $nativeDateTime);
        $this->assertSame('2023-05-15T10:30:45.123456Z+00:00', $nativeDateTime->format('Y-m-d\TH:i:s.u\ZP'));
    }

    // ------------------------------------------
    // 比較メソッドのテスト
    // ------------------------------------------

    #[Test]
    public function compareToで日時を比較できる(): void
    {
        $dateTime1 = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(10, 30, 0)
        );

        $dateTime2 = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(10, 30, 0)
        );

        $dateTime3 = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(10, 30, 1)
        );

        $dateTime4 = LocalDateTime::of(
            LocalDate::of(2023, 5, 16),
            LocalTime::of(9, 0, 0)
        );

        // 等しい場合は0
        $this->assertSame(0, $dateTime1->compareTo($dateTime2));

        // 同じ日で秒が異なる場合
        $this->assertSame(-1, $dateTime1->compareTo($dateTime3));
        $this->assertSame(1, $dateTime3->compareTo($dateTime1));

        // 日が異なる場合（時刻が異なっても日付優先）
        $this->assertSame(-1, $dateTime1->compareTo($dateTime4));
        $this->assertSame(1, $dateTime4->compareTo($dateTime1));
    }

    #[Test]
    public function 比較ヘルパーメソッドが正しく動作する(): void
    {
        $dateTime1 = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(10, 30, 0)
        );

        $dateTime2 = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(10, 30, 0)
        );

        $dateTime3 = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(10, 31, 0)
        );

        $this->assertTrue($dateTime1->isBeforeOrEqualTo($dateTime2));
        $this->assertTrue($dateTime1->isBeforeOrEqualTo($dateTime3));
        $this->assertFalse($dateTime3->isBeforeOrEqualTo($dateTime1));

        $this->assertFalse($dateTime1->isBefore($dateTime2));
        $this->assertTrue($dateTime1->isBefore($dateTime3));
        $this->assertFalse($dateTime3->isBefore($dateTime1));

        $this->assertTrue($dateTime1->isAfterOrEqualTo($dateTime2));
        $this->assertFalse($dateTime1->isAfterOrEqualTo($dateTime3));
        $this->assertTrue($dateTime3->isAfterOrEqualTo($dateTime1));

        $this->assertFalse($dateTime1->isAfter($dateTime2));
        $this->assertFalse($dateTime1->isAfter($dateTime3));
        $this->assertTrue($dateTime3->isAfter($dateTime1));
    }

    #[Test]
    public function isFutureで未来日時かどうかを判定できる(): void
    {
        // 現在より未来の日時
        $future = LocalDateTime::of(
            LocalDate::of(9999, 1, 1),
            LocalTime::of(0, 0, 0)
        );

        // 現在より過去の日時
        $past = LocalDateTime::of(
            LocalDate::of(2020, 1, 1),
            LocalTime::of(0, 0, 0)
        );

        $this->assertTrue($future->isFuture(new DateTimeZone('UTC')));
        $this->assertFalse($past->isFuture(new DateTimeZone('UTC')));
    }

    #[Test]
    public function isPastで過去日時かどうかを判定できる(): void
    {
        // 現在より未来の日時
        $future = LocalDateTime::of(
            LocalDate::of(9999, 1, 1),
            LocalTime::of(0, 0, 0)
        );

        // 現在より過去の日時
        $past = LocalDateTime::of(
            LocalDate::of(2020, 1, 1),
            LocalTime::of(0, 0, 0)
        );

        $this->assertFalse($future->isPast(new DateTimeZone('UTC')));
        $this->assertTrue($past->isPast(new DateTimeZone('UTC')));
    }

    // ------------------------------------------
    // 演算メソッドのテスト
    // ------------------------------------------

    #[Test]
    public function 日付操作メソッドが正しく動作する(): void
    {
        $dateTime = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(10, 30, 45)
        );

        // 年の加算
        $result1 = $dateTime->addYears(1);
        $this->assertSame(2024, $result1->getYear());
        $this->assertSame(5, $result1->getMonth());
        $this->assertSame(15, $result1->getDay());
        $this->assertSame(10, $result1->getHour());
        $this->assertSame(30, $result1->getMinute());
        $this->assertSame(45, $result1->getSecond());

        // 月の加算
        $result2 = $dateTime->addMonths(3);
        $this->assertSame(2023, $result2->getYear());
        $this->assertSame(8, $result2->getMonth());
        $this->assertSame(15, $result2->getDay());

        // 週の加算
        $result3 = $dateTime->addWeeks(2);
        $this->assertSame(2023, $result3->getYear());
        $this->assertSame(5, $result3->getMonth());
        $this->assertSame(29, $result3->getDay());

        // 日の加算
        $result4 = $dateTime->addDays(10);
        $this->assertSame(2023, $result4->getYear());
        $this->assertSame(5, $result4->getMonth());
        $this->assertSame(25, $result4->getDay());
    }

    #[Test]
    public function 時間操作メソッドが正しく動作する(): void
    {
        $dateTime = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(10, 30, 45, 500000)
        );

        // 時の加算
        $result1 = $dateTime->addHours(5);
        $this->assertSame(15, $result1->getHour());
        $this->assertSame(30, $result1->getMinute());
        $this->assertSame(45, $result1->getSecond());

        // 分の加算
        $result2 = $dateTime->addMinutes(40);
        $this->assertSame(11, $result2->getHour());
        $this->assertSame(10, $result2->getMinute());
        $this->assertSame(45, $result2->getSecond());

        // 秒の加算
        $result3 = $dateTime->addSeconds(20);
        $this->assertSame(10, $result3->getHour());
        $this->assertSame(31, $result3->getMinute());
        $this->assertSame(5, $result3->getSecond());

        // ナノ秒の加算
        $result4 = $dateTime->addMicros(600000);
        $this->assertSame(10, $result4->getHour());
        $this->assertSame(30, $result4->getMinute());
        $this->assertSame(46, $result4->getSecond());
        $this->assertSame(100000, $result4->getMicro());
    }

    #[Test]
    public function 日付をまたぐ時間操作が正しく動作する(): void
    {
        $dateTime = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(23, 30, 0)
        );

        // 1時間後は翌日
        $result1 = $dateTime->addHours(1);
        $this->assertSame(2023, $result1->getYear());
        $this->assertSame(5, $result1->getMonth());
        $this->assertSame(16, $result1->getDay());
        $this->assertSame(0, $result1->getHour());
        $this->assertSame(30, $result1->getMinute());

        // 月をまたぐケース
        $dateTime2 = LocalDateTime::of(
            LocalDate::of(2023, 5, 31),
            LocalTime::of(23, 30, 0)
        );

        $result2 = $dateTime2->addHours(1);
        $this->assertSame(2023, $result2->getYear());
        $this->assertSame(6, $result2->getMonth());
        $this->assertSame(1, $result2->getDay());
        $this->assertSame(0, $result2->getHour());
        $this->assertSame(30, $result2->getMinute());
    }

    #[Test]
    public function 加算メソッドの0値の場合は同じインスタンスが返る(): void
    {
        $dateTime = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(10, 30, 45)
        );

        $this->assertSame($dateTime, $dateTime->addYears(0));
        $this->assertSame($dateTime, $dateTime->addMonths(0));
        $this->assertSame($dateTime, $dateTime->addWeeks(0));
        $this->assertSame($dateTime, $dateTime->addDays(0));
        $this->assertSame($dateTime, $dateTime->addHours(0));
        $this->assertSame($dateTime, $dateTime->addMinutes(0));
        $this->assertSame($dateTime, $dateTime->addSeconds(0));
        $this->assertSame($dateTime, $dateTime->addMicros(0));
    }

    #[Test]
    public function 減算メソッドが正しく動作する(): void
    {
        $dateTime = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(10, 30, 45)
        );

        $this->assertSame(2022, $dateTime->subYears(1)->getYear());
        $this->assertSame(4, $dateTime->subMonths(1)->getMonth());
        $this->assertSame(1, $dateTime->subWeeks(2)->getDay());
        $this->assertSame(14, $dateTime->subDays(1)->getDay());
        $this->assertSame(9, $dateTime->subHours(1)->getHour());
        $this->assertSame(0, $dateTime->subMinutes(30)->getMinute());
        $this->assertSame(15, $dateTime->subSeconds(30)->getSecond());
        $this->assertSame(0, $dateTime->subMicros(45000000)->getSecond());
    }

    // ------------------------------------------
    // ValueObjectインターフェース実装のテスト
    // ------------------------------------------

    #[Test]
    public function equalsで同値性比較ができる(): void
    {
        $dateTime1 = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(10, 30, 0)
        );

        $dateTime2 = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(10, 30, 0)
        );

        $dateTime3 = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(10, 31, 0)
        );

        $this->assertTrue($dateTime1->equals($dateTime2));
        $this->assertFalse($dateTime1->equals($dateTime3));
    }

    #[Test]
    public function toStringでISO形式の文字列が取得できる(): void
    {
        $dateTime = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(10, 30, 45)
        );

        $this->assertSame('2023-05-15T10:30:45', (string)$dateTime);
    }

    #[Test]
    public function jsonSerializeでJSON形式の文字列が取得できる(): void
    {
        $dateTime = LocalDateTime::of(
            LocalDate::of(2023, 5, 15),
            LocalTime::of(10, 30, 45)
        );

        $this->assertSame('2023-05-15T10:30:45', $dateTime->jsonSerialize());
        $this->assertSame('"2023-05-15T10:30:45"', json_encode($dateTime));
    }

    // ------------------------------------------
    // アクセス制御のテスト
    // ------------------------------------------

    #[Test]
    public function コンストラクタはprivateアクセス修飾子を持つ(): void
    {
        $reflectionClass = new ReflectionClass(LocalDateTime::class);
        $constructor = $reflectionClass->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPrivate());
    }

    #[Test]
    public function privateコンストラクタへのアクセスを試みるとエラーとなる(): void
    {
        $this->expectException(Error::class);

        $date = LocalDate::of(2023, 5, 15);
        $time = LocalTime::of(10, 30, 45);

        /** @phpstan-ignore-next-line */
        $dateTime = new LocalDateTime($date, $time);
    }
}
