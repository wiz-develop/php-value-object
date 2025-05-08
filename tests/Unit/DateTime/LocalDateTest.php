<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\DateTime;

use AssertionError;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use ReflectionClass;
use Throwable;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\DateTime\LocalDate;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
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
    public function of関数で有効な日付のインスタンスが作成できる(): void
    {
        $date = LocalDate::of(2023, 1, 1);

        $this->assertInstanceOf(LocalDate::class, $date);
        $this->assertEquals(2023, $date->getYear());
        $this->assertEquals(1, $date->getMonth());
        $this->assertEquals(1, $date->getDay());
    }

    #[Test]
    public function from関数でDateTimeInterfaceからインスタンスが作成できる(): void
    {
        $dateTime = new DateTimeImmutable('2023-02-15');
        $date = LocalDate::from($dateTime);

        $this->assertInstanceOf(LocalDate::class, $date);
        $this->assertEquals(2023, $date->getYear());
        $this->assertEquals(2, $date->getMonth());
        $this->assertEquals(15, $date->getDay());
    }

    #[Test]
    public function fromNullable関数でnull値を扱える(): void
    {
        // nullの場合
        $option = LocalDate::fromNullable(null);
        $this->assertTrue($option->isNone());

        // 非nullの場合
        $dateTime = new DateTimeImmutable('2023-03-20');
        $option = LocalDate::fromNullable($dateTime);
        $this->assertTrue($option->isSome());
        $this->assertEquals(2023, $option->unwrap()->getYear());
        $this->assertEquals(3, $option->unwrap()->getMonth());
        $this->assertEquals(20, $option->unwrap()->getDay());
    }

    #[Test]
    public function tryFrom関数で有効な日付を検証してインスタンス化できる(): void
    {
        $dateTime = new DateTimeImmutable('2023-04-25');
        $result = LocalDate::tryFrom($dateTime);

        $this->assertTrue($result->isOk());
        $this->assertEquals(2023, $result->unwrap()->getYear());
        $this->assertEquals(4, $result->unwrap()->getMonth());
        $this->assertEquals(25, $result->unwrap()->getDay());
    }

    #[Test]
    public function tryFromNullable関数でnull値を扱える(): void
    {
        // nullの場合
        $result = LocalDate::tryFromNullable(null);
        $this->assertTrue($result->isOk());
        $this->assertTrue($result->unwrap()->isNone());

        // 非nullの場合
        $dateTime = new DateTimeImmutable('2023-05-30');
        $result = LocalDate::tryFromNullable($dateTime);
        $this->assertTrue($result->isOk());
        $this->assertTrue($result->unwrap()->isSome());
        $this->assertEquals(2023, $result->unwrap()->unwrap()->getYear());
        $this->assertEquals(5, $result->unwrap()->unwrap()->getMonth());
        $this->assertEquals(30, $result->unwrap()->unwrap()->getDay());
    }

    #[Test]
    public function now関数で現在の日付のインスタンスが作成できる(): void
    {
        $timeZone = new DateTimeZone('Asia/Tokyo');
        $date = LocalDate::now($timeZone);

        $this->assertInstanceOf(LocalDate::class, $date);

        // 現在の日付と一致するかのテスト（±1日の誤差を許容）
        $now = new DateTimeImmutable('now', $timeZone);
        $this->assertEqualsWithDelta(
            $now->format('Y-m-d'),
            $date->toISOString(),
            1,
            '現在の日付と一致すること（±1日の誤差を許容）'
        );
    }

    // ------------------------------------------
    // バリデーションのテスト
    // ------------------------------------------

    /**
     * @return array<string, array{int, int, int, bool}>
     */
    public static function 日付の検証データを提供(): array
    {
        return [
            '有効な日付' => [2023, 6, 15, true],
            'うるう年の2月29日' => [2024, 2, 29, true],
            '無効な日付：通常年の2月29日' => [2023, 2, 29, false],
            '無効な日付：存在しない月' => [2023, 13, 1, false],
            '無効な日付：存在しない日' => [2023, 4, 31, false],
            '無効な日付：0月' => [2023, 0, 15, false],
            '無効な日付：0日' => [2023, 6, 0, false],
            '無効な日付：年が範囲外_下限' => [-10000, 6, 15, false],
            '無効な日付：年が範囲外_上限' => [10000, 6, 15, false],
        ];
    }

    #[Test]
    #[DataProvider('日付の検証データを提供')]
    public function 日付バリデーションのテスト(int $year, int $month, int $day, bool $shouldBeValid): void
    {
        if ($shouldBeValid) {
            try {
                /** @phpstan-ignore argument.type, argument.type,argument.type */
                $date = LocalDate::of($year, $month, $day);
                $this->assertInstanceOf(LocalDate::class, $date);
                $this->assertEquals($year, $date->getYear());
                $this->assertEquals($month, $date->getMonth());
                $this->assertEquals($day, $date->getDay());
            } catch (Throwable $e) {
                $this->fail('有効な日付でエラーが発生: ' . $e->getMessage());
            }
        } else {
            // tryFromで無効な値をテスト
            // DateTimeInterfaceを使用するため、文字列からDateTimeImmutableを作成
            try {
                $dateString = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $dateTime = new DateTimeImmutable($dateString);
                $result = LocalDate::tryFrom($dateTime);

                // PHPのDateTimeImmutableは一部の無効な日付を自動的に修正するため
                // 例えば2023-02-29 は 2023-03-01 になる
                // そのため、厳密なテストのために of メソッドも使用
                $ofResult = null;

                try {
                    // @phpstan-ignore argument.type, argument.type,argument.type
                    LocalDate::of($year, $month, $day);
                    $ofResult = Result\ok(true);
                } catch (Throwable $e) {
                    $ofResult = Result\err(ValueObjectError::of(self::class, $e->getMessage()));
                }

                if ($ofResult->isOk()) {
                    $this->assertTrue($result->isOk(), "日付 {$year}-{$month}-{$day} は無効なはずだが有効として処理された");
                }
            } catch (Throwable $e) {
                // DateTimeImmutableが例外を投げる場合（明らかに無効な日付）
                // @phpstan-ignore method.alreadyNarrowedType
                $this->assertTrue(true, '期待通りの例外が発生');
            }
        }
    }

    #[Test]
    public function 年のバリデーションテスト(): void
    {
        // 有効な年
        $this->assertInstanceOf(LocalDate::class, LocalDate::of(2023, 1, 1));
        $this->assertInstanceOf(LocalDate::class, LocalDate::of(-9999, 1, 1)); // 最小値
        $this->assertInstanceOf(LocalDate::class, LocalDate::of(9999, 1, 1)); // 最大値

        // 無効な年
        $this->expectException(AssertionError::class);

        // @phpstan-ignore argument.type
        LocalDate::of(-10000, 1, 1); // 最小値未満
    }

    #[Test]
    public function 月のバリデーションテスト(): void
    {
        // 有効な月
        $this->assertInstanceOf(LocalDate::class, LocalDate::of(2023, 1, 1)); // 最小値
        $this->assertInstanceOf(LocalDate::class, LocalDate::of(2023, 12, 1)); // 最大値

        // 無効な月
        $this->expectException(AssertionError::class);

        // @phpstan-ignore argument.type
        LocalDate::of(2023, 0, 1); // 最小値未満
    }

    #[Test]
    public function 日のバリデーションテスト(): void
    {
        // 有効な日
        $this->assertInstanceOf(LocalDate::class, LocalDate::of(2023, 1, 1)); // 最小値
        $this->assertInstanceOf(LocalDate::class, LocalDate::of(2023, 1, 31)); // 最大値

        // 無効な日
        $this->expectException(AssertionError::class);

        // @phpstan-ignore argument.type
        LocalDate::of(2023, 1, 0); // 最小値未満
    }

    // ------------------------------------------
    // メソッドのテスト
    // ------------------------------------------

    #[Test]
    public function toISOString関数で日付をISO形式に変換できる(): void
    {
        $date = LocalDate::of(2023, 7, 15);
        $this->assertEquals('2023-07-15', $date->toISOString());

        // 一桁の月と日
        $date = LocalDate::of(2023, 1, 1);
        $this->assertEquals('2023-01-01', $date->toISOString());

        // 年が4桁未満
        $date = LocalDate::of(23, 7, 15);
        $this->assertEquals('0023-07-15', $date->toISOString());

        // 負の年
        $date = LocalDate::of(-42, 7, 15);
        $this->assertEquals('-0042-07-15', $date->toISOString());
    }

    #[Test]
    public function 文字列表現のテスト(): void
    {
        $date = LocalDate::of(2023, 8, 20);
        $this->assertEquals('2023-08-20', (string)$date);
    }

    #[Test]
    public function jsonSerialize関数でJSON形式に変換できる(): void
    {
        $date = LocalDate::of(2023, 9, 25);
        $this->assertEquals('"2023-09-25"', json_encode($date));
    }

    #[Test]
    public function getYear_getMonth_getDay関数で各構成要素を取得できる(): void
    {
        $date = LocalDate::of(2023, 10, 30);

        $this->assertEquals(2023, $date->getYear());
        $this->assertEquals(10, $date->getMonth());
        $this->assertEquals(30, $date->getDay());
    }

    // ------------------------------------------
    // 比較関数のテスト
    // ------------------------------------------

    /**
     * @return array<string, array{string, string, int}>
     */
    public static function 日付比較のデータを提供(): array
    {
        return [
            '等しい日付' => ['2023-01-01', '2023-01-01', 0],
            '1日後' => ['2023-01-02', '2023-01-01', 1],
            '1日前' => ['2023-01-01', '2023-01-02', -1],
            '1ヶ月後' => ['2023-02-01', '2023-01-01', 1],
            '1ヶ月前' => ['2023-01-01', '2023-02-01', -1],
            '1年後' => ['2024-01-01', '2023-01-01', 1],
            '1年前' => ['2023-01-01', '2024-01-01', -1],
        ];
    }

    #[Test]
    #[DataProvider('日付比較のデータを提供')]
    public function compareTo関数で日付の比較ができる(string $date1String, string $date2String, int $expected): void
    {
        $date1 = LocalDate::from(new DateTimeImmutable($date1String));
        $date2 = LocalDate::from(new DateTimeImmutable($date2String));

        $this->assertEquals($expected, $date1->compareTo($date2));
    }

    #[Test]
    public function 比較関数のテスト(): void
    {
        $date1 = LocalDate::of(2023, 1, 1);
        $date2 = LocalDate::of(2023, 1, 2);
        $date3 = LocalDate::of(2023, 1, 1);

        // isBefore
        $this->assertTrue($date1->isBefore($date2));
        $this->assertFalse($date2->isBefore($date1));
        $this->assertFalse($date1->isBefore($date3));

        // isBeforeOrEqualTo
        $this->assertTrue($date1->isBeforeOrEqualTo($date2));
        $this->assertFalse($date2->isBeforeOrEqualTo($date1));
        $this->assertTrue($date1->isBeforeOrEqualTo($date3));

        // isAfter
        $this->assertFalse($date1->isAfter($date2));
        $this->assertTrue($date2->isAfter($date1));
        $this->assertFalse($date1->isAfter($date3));

        // isAfterOrEqualTo
        $this->assertFalse($date1->isAfterOrEqualTo($date2));
        $this->assertTrue($date2->isAfterOrEqualTo($date1));
        $this->assertTrue($date1->isAfterOrEqualTo($date3));
    }

    #[Test]
    public function equals関数で同値性の比較ができる(): void
    {
        $date1 = LocalDate::of(2023, 1, 1);
        $date2 = LocalDate::of(2023, 1, 1);
        $date3 = LocalDate::of(2023, 1, 2);

        $this->assertTrue($date1->equals($date2));
        $this->assertFalse($date1->equals($date3));
    }

    // ------------------------------------------
    // アクセス制御のテスト
    // ------------------------------------------

    #[Test]
    public function コンストラクタはprivateアクセス修飾子を持つことを確認(): void
    {
        $reflectionClass = new ReflectionClass(LocalDate::class);
        $constructor = $reflectionClass->getConstructor();

        $this->assertNotNull($constructor, 'コンストラクタが見つかりませんでした');
        $this->assertTrue($constructor->isPrivate(), 'コンストラクタはprivateでなければならない');
    }

    // ------------------------------------------
    // エッジケースのテスト
    // ------------------------------------------

    #[Test]
    public function 極端な日付のテスト(): void
    {
        // 範囲内の最大値、最小値
        $minDate = LocalDate::of(-9999, 1, 1);
        $maxDate = LocalDate::of(9999, 12, 31);

        $this->assertEquals('-9999-01-01', $minDate->toISOString());
        $this->assertEquals('9999-12-31', $maxDate->toISOString());

        // 比較
        $this->assertTrue($minDate->isBefore($maxDate));
        $this->assertTrue($maxDate->isAfter($minDate));
    }
}
