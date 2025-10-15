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
 * LocalDateクラスのテスト
 */
#[TestDox('LocalDateクラスのテスト')]
#[Group('DateTime')]
#[CoversClass(LocalDate::class)]
final class LocalDateTest extends TestCase
{
    // ------------------------------------------
    // インスタンス生成と基本機能のテスト
    // ------------------------------------------

    #[Test]
    public function 有効な値でインスタンスが作成できる(): void
    {
        $date = LocalDate::of(2023, 5, 15);

        $this->assertSame(2023, $date->getYear());
        $this->assertSame(5, $date->getMonth());
        $this->assertSame(15, $date->getDay());
    }

    #[Test]
    public function DateTimeからインスタンスが作成できる(): void
    {
        $dateTime = new DateTimeImmutable('2023-05-15 10:30:45', new DateTimeZone('UTC'));
        $date = LocalDate::from($dateTime);

        $this->assertSame(2023, $date->getYear());
        $this->assertSame(5, $date->getMonth());
        $this->assertSame(15, $date->getDay());
    }

    #[Test]
    public function fromNullableでNullを扱える(): void
    {
        $option1 = LocalDate::fromNullable(null);
        $this->assertTrue($option1->isNone());

        $dateTime = new DateTimeImmutable('2023-05-15');
        $option2 = LocalDate::fromNullable($dateTime);
        $this->assertTrue($option2->isSome());
        $this->assertSame(2023, $option2->unwrap()->getYear());
        $this->assertSame(5, $option2->unwrap()->getMonth());
        $this->assertSame(15, $option2->unwrap()->getDay());
    }

    #[Test]
    public function tryFromNullableでNullを扱える(): void
    {
        $result1 = LocalDate::tryFromNullable(null);
        $this->assertTrue($result1->isOk());
        $this->assertTrue($result1->unwrap()->isNone());

        $dateTime = new DateTimeImmutable('2023-05-15');
        $result2 = LocalDate::tryFromNullable($dateTime);
        $this->assertTrue($result2->isOk());
        $this->assertTrue($result2->unwrap()->isSome());
        $date = $result2->unwrap()->unwrap();
        $this->assertSame(2023, $date->getYear());
        $this->assertSame(5, $date->getMonth());
        $this->assertSame(15, $date->getDay());
    }

    #[Test]
    public function nowで現在日付のインスタンスが作成できる(): void
    {
        $date = LocalDate::now(new DateTimeZone('UTC'));

        // 現在日付なので厳密な値のテストはできないが、今日の日付と一致するはず
        $today = new DateTimeImmutable('today', new DateTimeZone('UTC'));
        $this->assertSame((int)$today->format('Y'), $date->getYear());
        $this->assertSame((int)$today->format('n'), $date->getMonth());
        $this->assertSame((int)$today->format('j'), $date->getDay());
    }

    #[Test]
    public function ofEpochDayでエポック日からインスタンスが作成できる(): void
    {
        // 1970-01-01 (エポックのスタート)
        $date1 = LocalDate::ofEpochDay(0);
        $this->assertSame(1970, $date1->getYear());
        $this->assertSame(1, $date1->getMonth());
        $this->assertSame(1, $date1->getDay());

        // 2023-05-15
        $date2 = LocalDate::ofEpochDay(19492); // 2023-05-15のエポック日
        $this->assertSame(2023, $date2->getYear());
        $this->assertSame(5, $date2->getMonth());
        $this->assertSame(15, $date2->getDay());

        // 1969-12-31 (エポックの前日)
        $date3 = LocalDate::ofEpochDay(-1);
        $this->assertSame(1969, $date3->getYear());
        $this->assertSame(12, $date3->getMonth());
        $this->assertSame(31, $date3->getDay());
    }

    // ------------------------------------------
    // getterメソッドのテスト
    // ------------------------------------------

    #[Test]
    public function getterメソッドで各値が取得できる(): void
    {
        $date = LocalDate::of(2023, 5, 15);

        $this->assertSame(2023, $date->getYear());
        $this->assertSame(5, $date->getMonth());
        $this->assertSame(15, $date->getDay());
    }

    #[Test]
    public function getLengthOfMonthで月の日数が取得できる(): void
    {
        // 通常の年の2月（28日）
        $date1 = LocalDate::of(2023, 2, 1);
        $this->assertSame(28, $date1->getLengthOfMonth());

        // うるう年の2月（29日）
        $date2 = LocalDate::of(2024, 2, 1);
        $this->assertSame(29, $date2->getLengthOfMonth());

        // 30日の月（4, 6, 9, 11月）
        $date3 = LocalDate::of(2023, 4, 1);
        $this->assertSame(30, $date3->getLengthOfMonth());

        // 31日の月（1, 3, 5, 7, 8, 10, 12月）
        $date4 = LocalDate::of(2023, 5, 1);
        $this->assertSame(31, $date4->getLengthOfMonth());
    }

    // ------------------------------------------
    // 変換メソッドのテスト
    // ------------------------------------------

    #[Test]
    public function toISOStringでISO形式の文字列が取得できる(): void
    {
        $date1 = LocalDate::of(2023, 5, 15);
        $this->assertSame('2023-05-15', $date1->toISOString());

        $date2 = LocalDate::of(2023, 12, 1);
        $this->assertSame('2023-12-01', $date2->toISOString());

        // 1000年未満の場合
        $date3 = LocalDate::of(987, 6, 7);
        $this->assertSame('0987-06-07', $date3->toISOString());

        // 負の年の場合
        $date4 = LocalDate::of(-42, 8, 9);
        $this->assertSame('-0042-08-09', $date4->toISOString());
    }

    #[Test]
    public function toEpochDayでエポック日が取得できる(): void
    {
        // 1970-01-01 (エポックのスタート)
        $date1 = LocalDate::of(1970, 1, 1);
        $this->assertSame(0, $date1->toEpochDay());

        // 2023-05-15
        $date2 = LocalDate::of(2023, 5, 15);
        $this->assertSame(19492, $date2->toEpochDay());

        // 1969-12-31 (エポックの前日)
        $date3 = LocalDate::of(1969, 12, 31);
        $this->assertSame(-1, $date3->toEpochDay());
    }

    #[Test]
    public function toDateTimeImmutableでDateTimeImmutableに変換できる(): void
    {
        $date = LocalDate::of(2023, 5, 15);
        $dateTime = $date->toDateTimeImmutable();

        $this->assertInstanceOf(DateTimeImmutable::class, $dateTime);
        $this->assertSame('2023-05-15T00:00:00+00:00', $dateTime->format('c'));
    }

    #[Test]
    public function atTimeでLocalDateTimeに変換できる(): void
    {
        $date = LocalDate::of(2023, 5, 15);
        $time = LocalTime::of(10, 30, 45);

        $dateTime = $date->atTime($time);

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
    public function compareToで日付を比較できる(): void
    {
        $date1 = LocalDate::of(2023, 5, 15);
        $date2 = LocalDate::of(2023, 5, 15);
        $date3 = LocalDate::of(2023, 5, 16);
        $date4 = LocalDate::of(2023, 6, 1);
        $date5 = LocalDate::of(2024, 1, 1);

        // 等しい場合は0
        $this->assertSame(0, $date1->compareTo($date2));

        // 日が異なる場合
        $this->assertSame(-1, $date1->compareTo($date3));
        $this->assertSame(1, $date3->compareTo($date1));

        // 月が異なる場合
        $this->assertSame(-1, $date1->compareTo($date4));
        $this->assertSame(1, $date4->compareTo($date1));

        // 年が異なる場合
        $this->assertSame(-1, $date1->compareTo($date5));
        $this->assertSame(1, $date5->compareTo($date1));
    }

    #[Test]
    public function 比較ヘルパーメソッドが正しく動作する(): void
    {
        $date1 = LocalDate::of(2023, 5, 15);
        $date2 = LocalDate::of(2023, 5, 15);
        $date3 = LocalDate::of(2023, 5, 16);

        $this->assertTrue($date1->isBeforeOrEqualTo($date2));
        $this->assertTrue($date1->isBeforeOrEqualTo($date3));
        $this->assertFalse($date3->isBeforeOrEqualTo($date1));

        $this->assertFalse($date1->isBefore($date2));
        $this->assertTrue($date1->isBefore($date3));
        $this->assertFalse($date3->isBefore($date1));

        $this->assertTrue($date1->isAfterOrEqualTo($date2));
        $this->assertFalse($date1->isAfterOrEqualTo($date3));
        $this->assertTrue($date3->isAfterOrEqualTo($date1));

        $this->assertFalse($date1->isAfter($date2));
        $this->assertFalse($date1->isAfter($date3));
        $this->assertTrue($date3->isAfter($date1));
    }

    // ------------------------------------------
    // 演算メソッドのテスト
    // ------------------------------------------

    #[Test]
    public function addYearsで年を加算できる(): void
    {
        $date1 = LocalDate::of(2023, 5, 15);

        $result1 = $date1->addYears(1);
        $this->assertSame(2024, $result1->getYear());
        $this->assertSame(5, $result1->getMonth());
        $this->assertSame(15, $result1->getDay());

        $result2 = $date1->addYears(-3);
        $this->assertSame(2020, $result2->getYear());
        $this->assertSame(5, $result2->getMonth());
        $this->assertSame(15, $result2->getDay());

        // うるう年の2月29日の処理
        $leapDate = LocalDate::of(2024, 2, 29);
        $result3 = $leapDate->addYears(1);
        $this->assertSame(2025, $result3->getYear());
        $this->assertSame(2, $result3->getMonth());
        $this->assertSame(28, $result3->getDay()); // 2025年2月は28日まで
    }

    #[Test]
    public function addMonthsで月を加算できる(): void
    {
        $date1 = LocalDate::of(2023, 5, 15);

        $result1 = $date1->addMonths(1);
        $this->assertSame(2023, $result1->getYear());
        $this->assertSame(6, $result1->getMonth());
        $this->assertSame(15, $result1->getDay());

        $result2 = $date1->addMonths(8);
        $this->assertSame(2024, $result2->getYear());
        $this->assertSame(1, $result2->getMonth());
        $this->assertSame(15, $result2->getDay());

        $result3 = $date1->addMonths(-6);
        $this->assertSame(2022, $result3->getYear());
        $this->assertSame(11, $result3->getMonth());
        $this->assertSame(15, $result3->getDay());

        // 月末の処理
        $monthEnd = LocalDate::of(2023, 1, 31);
        $result4 = $monthEnd->addMonths(1);
        $this->assertSame(2023, $result4->getYear());
        $this->assertSame(2, $result4->getMonth());
        $this->assertSame(28, $result4->getDay()); // 2月は28日まで
    }

    #[Test]
    public function addWeeksで週を加算できる(): void
    {
        $date1 = LocalDate::of(2023, 5, 15);

        $result1 = $date1->addWeeks(1);
        $this->assertSame(2023, $result1->getYear());
        $this->assertSame(5, $result1->getMonth());
        $this->assertSame(22, $result1->getDay());

        $result2 = $date1->addWeeks(3);
        $this->assertSame(2023, $result2->getYear());
        $this->assertSame(6, $result2->getMonth());
        $this->assertSame(5, $result2->getDay());

        $result3 = $date1->addWeeks(-2);
        $this->assertSame(2023, $result3->getYear());
        $this->assertSame(5, $result3->getMonth());
        $this->assertSame(1, $result3->getDay());
    }

    #[Test]
    public function addDaysで日を加算できる(): void
    {
        $date1 = LocalDate::of(2023, 5, 15);

        $result1 = $date1->addDays(1);
        $this->assertSame(2023, $result1->getYear());
        $this->assertSame(5, $result1->getMonth());
        $this->assertSame(16, $result1->getDay());

        $result2 = $date1->addDays(20);
        $this->assertSame(2023, $result2->getYear());
        $this->assertSame(6, $result2->getMonth());
        $this->assertSame(4, $result2->getDay());

        $result3 = $date1->addDays(-20);
        $this->assertSame(2023, $result3->getYear());
        $this->assertSame(4, $result3->getMonth());
        $this->assertSame(25, $result3->getDay());
    }

    #[Test]
    public function 加算メソッドの0値の場合は同じインスタンスが返る(): void
    {
        $date = LocalDate::of(2023, 5, 15);

        $this->assertSame($date, $date->addYears(0));
        $this->assertSame($date, $date->addMonths(0));
        $this->assertSame($date, $date->addWeeks(0));
        $this->assertSame($date, $date->addDays(0));
    }

    #[Test]
    public function 減算メソッドが正しく動作する(): void
    {
        $date = LocalDate::of(2023, 5, 15);

        $this->assertSame(2022, $date->subYears(1)->getYear());
        $this->assertSame(4, $date->subMonths(1)->getMonth());
        $this->assertSame(8, $date->subWeeks(1)->getDay());
        $this->assertSame(14, $date->subDays(1)->getDay());
    }

    // ------------------------------------------
    // ValueObjectインターフェース実装のテスト
    // ------------------------------------------

    #[Test]
    public function equalsで同値性比較ができる(): void
    {
        $date1 = LocalDate::of(2023, 5, 15);
        $date2 = LocalDate::of(2023, 5, 15);
        $date3 = LocalDate::of(2023, 5, 16);

        $this->assertTrue($date1->equals($date2));
        $this->assertFalse($date1->equals($date3));
    }

    #[Test]
    public function toStringでISO形式の文字列が取得できる(): void
    {
        $date = LocalDate::of(2023, 5, 15);

        $this->assertSame('2023-05-15', (string)$date);
    }

    #[Test]
    public function jsonSerializeでJSON形式の文字列が取得できる(): void
    {
        $date = LocalDate::of(2023, 5, 15);

        $this->assertSame('2023-05-15', $date->jsonSerialize());
        $this->assertSame('"2023-05-15"', json_encode($date));
    }

    // ------------------------------------------
    // アクセス制御のテスト
    // ------------------------------------------

    #[Test]
    public function コンストラクタはprivateアクセス修飾子を持つ(): void
    {
        $reflectionClass = new ReflectionClass(LocalDate::class);
        $constructor = $reflectionClass->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPrivate());
    }

    #[Test]
    public function privateコンストラクタへのアクセスを試みるとエラーとなる(): void
    {
        $this->expectException(Error::class);

        /** @phpstan-ignore-next-line */
        $date = new LocalDate(2023, 5, 15);
    }

    #[Test]
    public function maxメソッドで正常にインスタンスが作成できる(): void
    {
        $date = LocalDate::max();

        $this->assertSame(LocalDate::MAX_YEAR, $date->getYear());
        $this->assertSame(LocalDate::MAX_MONTH, $date->getMonth());
        $this->assertSame(LocalDate::MAX_DAY, $date->getDay());
    }

    #[Test]
    public function minメソッドで正常にインスタンスが作成できる(): void
    {
        $date = LocalDate::min();
        $this->assertSame(LocalDate::MIN_YEAR, $date->getYear());
        $this->assertSame(LocalDate::MIN_MONTH, $date->getMonth());
        $this->assertSame(LocalDate::MIN_DAY, $date->getDay());
    }
}
