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
 * LocalTimeクラスのテスト
 */
#[TestDox('LocalTimeクラスのテスト')]
#[Group('DateTime')]
#[CoversClass(LocalTime::class)]
final class LocalTimeTest extends TestCase
{
    // ------------------------------------------
    // インスタンス生成と基本機能のテスト
    // ------------------------------------------

    #[Test]
    public function 有効な値でインスタンスが作成できる(): void
    {
        $time1 = LocalTime::of(10, 30);
        $this->assertSame(10, $time1->getHour());
        $this->assertSame(30, $time1->getMinute());
        $this->assertSame(0, $time1->getSecond());
        $this->assertSame(0, $time1->getMicro());

        $time2 = LocalTime::of(15, 45, 20);
        $this->assertSame(15, $time2->getHour());
        $this->assertSame(45, $time2->getMinute());
        $this->assertSame(20, $time2->getSecond());
        $this->assertSame(0, $time2->getMicro());

        $time3 = LocalTime::of(23, 59, 59, 999999);
        $this->assertSame(23, $time3->getHour());
        $this->assertSame(59, $time3->getMinute());
        $this->assertSame(59, $time3->getSecond());
        $this->assertSame(999999, $time3->getMicro());
    }

    #[Test]
    public function ofSecondOfDayで秒からインスタンスが作成できる(): void
    {
        // 00:00:00
        $time1 = LocalTime::ofSecondOfDay(0);
        $this->assertSame(0, $time1->getHour());
        $this->assertSame(0, $time1->getMinute());
        $this->assertSame(0, $time1->getSecond());

        // 01:00:00
        $time2 = LocalTime::ofSecondOfDay(3600);
        $this->assertSame(1, $time2->getHour());
        $this->assertSame(0, $time2->getMinute());
        $this->assertSame(0, $time2->getSecond());

        // 12:30:45
        $time3 = LocalTime::ofSecondOfDay(45045);
        $this->assertSame(12, $time3->getHour());
        $this->assertSame(30, $time3->getMinute());
        $this->assertSame(45, $time3->getSecond());

        // マイクロ秒も指定
        $time4 = LocalTime::ofSecondOfDay(45045, 500000);
        $this->assertSame(500000, $time4->getMicro());
    }

    #[Test]
    public function DateTimeからインスタンスが作成できる(): void
    {
        $dateTime = new DateTimeImmutable('2023-05-15 10:30:45.123456', new DateTimeZone('UTC'));
        $time = LocalTime::from($dateTime);

        $this->assertSame(10, $time->getHour());
        $this->assertSame(30, $time->getMinute());
        $this->assertSame(45, $time->getSecond());
        $this->assertSame(123456, $time->getMicro());
    }

    #[Test]
    public function fromNullableでNullを扱える(): void
    {
        $option1 = LocalTime::fromNullable(null);
        $this->assertTrue($option1->isNone());

        $dateTime = new DateTimeImmutable('10:30:45');
        $option2 = LocalTime::fromNullable($dateTime);
        $this->assertTrue($option2->isSome());
        $this->assertSame(10, $option2->unwrap()->getHour());
        $this->assertSame(30, $option2->unwrap()->getMinute());
        $this->assertSame(45, $option2->unwrap()->getSecond());
    }

    #[Test]
    public function tryFromNullableでNullを扱える(): void
    {
        $result1 = LocalTime::tryFromNullable(null);
        $this->assertTrue($result1->isOk());
        $this->assertTrue($result1->unwrap()->isNone());

        $dateTime = new DateTimeImmutable('10:30:45');
        $result2 = LocalTime::tryFromNullable($dateTime);
        $this->assertTrue($result2->isOk());
        $this->assertTrue($result2->unwrap()->isSome());
        $time = $result2->unwrap()->unwrap();
        $this->assertSame(10, $time->getHour());
        $this->assertSame(30, $time->getMinute());
        $this->assertSame(45, $time->getSecond());
    }

    #[Test]
    public function nowで現在時刻のインスタンスが作成できる(): void
    {
        $time = LocalTime::now(new DateTimeZone('UTC'));

        // 現在時刻なので厳密な値のテストはできないが、一応現在のHour内にあるはず
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $this->assertSame((int)$now->format('G'), $time->getHour());
    }

    #[Test]
    public function midnightで午前0時のインスタンスが作成できる(): void
    {
        $time = LocalTime::midnight();

        $this->assertSame(0, $time->getHour());
        $this->assertSame(0, $time->getMinute());
        $this->assertSame(0, $time->getSecond());
        $this->assertSame(0, $time->getMicro());
    }

    #[Test]
    public function minで最小時刻のインスタンスが作成できる(): void
    {
        $time = LocalTime::min();

        $this->assertSame(0, $time->getHour());
        $this->assertSame(0, $time->getMinute());
        $this->assertSame(0, $time->getSecond());
        $this->assertSame(0, $time->getMicro());
    }

    // ------------------------------------------
    // getterメソッドのテスト
    // ------------------------------------------

    #[Test]
    public function getterメソッドで各値が取得できる(): void
    {
        $time = LocalTime::of(10, 30, 45, 123456);

        $this->assertSame(10, $time->getHour());
        $this->assertSame(30, $time->getMinute());
        $this->assertSame(45, $time->getSecond());
        $this->assertSame(123456, $time->getMicro());
    }

    #[Test]
    public function toSecondOfDayで1日の秒数が取得できる(): void
    {
        $time1 = LocalTime::of(0, 0, 0);
        $this->assertSame(0, $time1->toSecondOfDay());

        $time2 = LocalTime::of(1, 0, 0);
        $this->assertSame(3600, $time2->toSecondOfDay());

        $time3 = LocalTime::of(1, 30, 0);
        $this->assertSame(5400, $time3->toSecondOfDay());

        $time4 = LocalTime::of(12, 34, 56);
        $this->assertSame(45296, $time4->toSecondOfDay());

        $time5 = LocalTime::of(23, 59, 59);
        $this->assertSame(86399, $time5->toSecondOfDay());
    }

    // ------------------------------------------
    // 変換メソッドのテスト
    // ------------------------------------------

    #[Test]
    public function toISOStringでISO形式の文字列が取得できる(): void
    {
        // 時:分
        $time1 = LocalTime::of(10, 30);
        $this->assertSame('10:30', $time1->toISOString());

        // 時:分:秒
        $time2 = LocalTime::of(10, 30, 45);
        $this->assertSame('10:30:45', $time2->toISOString());

        // 時:分:秒.マイクロ秒
        $time3 = LocalTime::of(10, 30, 45, 123456);
        $this->assertSame('10:30:45.123456', $time3->toISOString());

        // マイクロ秒の末尾の0は削除される
        $time4 = LocalTime::of(10, 30, 45, 123000);
        $this->assertSame('10:30:45.123', $time4->toISOString());

        // 時、分が1桁の場合は0埋め
        $time5 = LocalTime::of(1, 5, 9);
        $this->assertSame('01:05:09', $time5->toISOString());
    }

    #[Test]
    public function toDateTimeImmutableでDateTimeImmutableに変換できる(): void
    {
        $time = LocalTime::of(10, 30, 45, 123456);
        $dateTime = $time->toDateTimeImmutable();

        $this->assertInstanceOf(DateTimeImmutable::class, $dateTime);
        $this->assertSame('0000-01-01T10:30:45.123456Z+00:00', $dateTime->format('Y-m-d\TH:i:s.u\ZP'));
    }

    #[Test]
    public function atDateでLocalDateTimeに変換できる(): void
    {
        $time = LocalTime::of(10, 30, 45);
        $date = LocalDate::of(2023, 5, 15);

        $dateTime = $time->atDate($date);

        $this->assertInstanceOf(LocalDateTime::class, $dateTime);
        $this->assertSame(2023, $dateTime->getYear());
        $this->assertSame(5, $dateTime->getMonth());
        $this->assertSame(15, $dateTime->getDay());
        $this->assertSame(10, $dateTime->getHour());
        $this->assertSame(30, $dateTime->getMinute());
        $this->assertSame(45, $dateTime->getSecond());
    }

    // ------------------------------------------
    // 比較メソッドのテスト
    // ------------------------------------------

    #[Test]
    public function compareToで時刻を比較できる(): void
    {
        $time1 = LocalTime::of(10, 30, 0);
        $time2 = LocalTime::of(10, 30, 0);
        $time3 = LocalTime::of(10, 30, 1);
        $time4 = LocalTime::of(10, 31, 0);
        $time5 = LocalTime::of(11, 0, 0);
        $time6 = LocalTime::of(10, 30, 0, 1);

        // 等しい場合は0
        $this->assertSame(0, $time1->compareTo($time2));

        // 秒が異なる場合
        $this->assertSame(-1, $time1->compareTo($time3));
        $this->assertSame(1, $time3->compareTo($time1));

        // 分が異なる場合
        $this->assertSame(-1, $time1->compareTo($time4));
        $this->assertSame(1, $time4->compareTo($time1));

        // 時が異なる場合
        $this->assertSame(-1, $time1->compareTo($time5));
        $this->assertSame(1, $time5->compareTo($time1));

        // マイクロ秒が異なる場合
        $this->assertSame(-1, $time1->compareTo($time6));
        $this->assertSame(1, $time6->compareTo($time1));
    }

    #[Test]
    public function 比較ヘルパーメソッドが正しく動作する(): void
    {
        $time1 = LocalTime::of(10, 30);
        $time2 = LocalTime::of(10, 30);
        $time3 = LocalTime::of(10, 31);

        $this->assertTrue($time1->isBeforeOrEqualTo($time2));
        $this->assertTrue($time1->isBeforeOrEqualTo($time3));
        $this->assertFalse($time3->isBeforeOrEqualTo($time1));

        $this->assertFalse($time1->isBefore($time2));
        $this->assertTrue($time1->isBefore($time3));
        $this->assertFalse($time3->isBefore($time1));

        $this->assertTrue($time1->isAfterOrEqualTo($time2));
        $this->assertFalse($time1->isAfterOrEqualTo($time3));
        $this->assertTrue($time3->isAfterOrEqualTo($time1));

        $this->assertFalse($time1->isAfter($time2));
        $this->assertFalse($time1->isAfter($time3));
        $this->assertTrue($time3->isAfter($time1));
    }

    // ------------------------------------------
    // 演算メソッドのテスト
    // ------------------------------------------

    #[Test]
    public function addHoursで時間を加算できる(): void
    {
        $time = LocalTime::of(10, 30);

        $result1 = $time->addHours(1);
        $this->assertSame(11, $result1->getHour());
        $this->assertSame(30, $result1->getMinute());

        $result2 = $time->addHours(14);
        $this->assertSame(0, $result2->getHour());
        $this->assertSame(30, $result2->getMinute());

        $result3 = $time->addHours(-12);
        $this->assertSame(22, $result3->getHour());
        $this->assertSame(30, $result3->getMinute());
    }

    #[Test]
    public function addMinutesで分を加算できる(): void
    {
        $time = LocalTime::of(10, 30);

        $result1 = $time->addMinutes(15);
        $this->assertSame(10, $result1->getHour());
        $this->assertSame(45, $result1->getMinute());

        $result2 = $time->addMinutes(40);
        $this->assertSame(11, $result2->getHour());
        $this->assertSame(10, $result2->getMinute());

        $result3 = $time->addMinutes(-40);
        $this->assertSame(9, $result3->getHour());
        $this->assertSame(50, $result3->getMinute());

        // 1日を超える加算
        $result4 = $time->addMinutes(1440);
        $this->assertSame(10, $result4->getHour());
        $this->assertSame(30, $result4->getMinute());
    }

    #[Test]
    public function addSecondsで秒を加算できる(): void
    {
        $time = LocalTime::of(10, 30, 15);

        $result1 = $time->addSeconds(30);
        $this->assertSame(10, $result1->getHour());
        $this->assertSame(30, $result1->getMinute());
        $this->assertSame(45, $result1->getSecond());

        $result2 = $time->addSeconds(60);
        $this->assertSame(10, $result2->getHour());
        $this->assertSame(31, $result2->getMinute());
        $this->assertSame(15, $result2->getSecond());

        $result3 = $time->addSeconds(-20);
        $this->assertSame(10, $result3->getHour());
        $this->assertSame(29, $result3->getMinute());
        $this->assertSame(55, $result3->getSecond());
    }

    #[Test]
    public function addMicrosでマイクロ秒を加算できる(): void
    {
        $time = LocalTime::of(10, 30, 15, 500000);

        $result1 = $time->addMicros(300000);
        $this->assertSame(10, $result1->getHour());
        $this->assertSame(30, $result1->getMinute());
        $this->assertSame(15, $result1->getSecond());
        $this->assertSame(800000, $result1->getMicro());

        $result2 = $time->addMicros(600000);
        $this->assertSame(10, $result2->getHour());
        $this->assertSame(30, $result2->getMinute());
        $this->assertSame(16, $result2->getSecond());
        $this->assertSame(100000, $result2->getMicro());

        $result3 = $time->addMicros(-600000);
        $this->assertSame(10, $result3->getHour());
        $this->assertSame(30, $result3->getMinute());
        $this->assertSame(14, $result3->getSecond());
        $this->assertSame(900000, $result3->getMicro());
    }

    #[Test]
    public function 加算メソッドの0値の場合は同じインスタンスが返る(): void
    {
        $time = LocalTime::of(10, 30, 15);

        $this->assertSame($time, $time->addHours(0));
        $this->assertSame($time, $time->addMinutes(0));
        $this->assertSame($time, $time->addSeconds(0));
        $this->assertSame($time, $time->addMicros(0));
    }

    #[Test]
    public function 減算メソッドが正しく動作する(): void
    {
        $time = LocalTime::of(10, 30, 15);

        $this->assertSame(9, $time->subHours(1)->getHour());
        $this->assertSame(0, $time->subMinutes(30)->getMinute());
        $this->assertSame(0, $time->subSeconds(15)->getSecond());

        $time2 = LocalTime::of(10, 30, 15, 500000);
        $this->assertSame(0, $time2->subMicros(500000)->getMicro());
    }

    // ------------------------------------------
    // ValueObjectインターフェース実装のテスト
    // ------------------------------------------

    #[Test]
    public function equalsで同値性比較ができる(): void
    {
        $time1 = LocalTime::of(10, 30);
        $time2 = LocalTime::of(10, 30);
        $time3 = LocalTime::of(10, 31);

        $this->assertTrue($time1->equals($time2));
        $this->assertFalse($time1->equals($time3));
    }

    #[Test]
    public function toStringでISO形式の文字列が取得できる(): void
    {
        $time = LocalTime::of(10, 30, 45);

        $this->assertSame('10:30:45', (string)$time);
    }

    #[Test]
    public function jsonSerializeでJSON形式の文字列が取得できる(): void
    {
        $time = LocalTime::of(10, 30, 45);

        $this->assertSame('10:30:45', $time->jsonSerialize());
        $this->assertSame('"10:30:45"', json_encode($time));
    }

    // ------------------------------------------
    // アクセス制御のテスト
    // ------------------------------------------

    #[Test]
    public function コンストラクタはprivateアクセス修飾子を持つ(): void
    {
        $reflectionClass = new ReflectionClass(LocalTime::class);
        $constructor = $reflectionClass->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPrivate());
    }

    #[Test]
    public function privateコンストラクタへのアクセスを試みるとエラーとなる(): void
    {
        $this->expectException(Error::class);

        /** @phpstan-ignore-next-line */
        $time = new LocalTime(10, 30, 0, 0);
    }
}
