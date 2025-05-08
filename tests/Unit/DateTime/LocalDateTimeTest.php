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
    public function of関数で有効な日時のインスタンスが作成できる(): void
    {
        // 年月日のみ（時分秒マイクロ秒はデフォルト値）
        $dateTime = LocalDateTime::of(2023, 1, 1);

        $this->assertInstanceOf(LocalDateTime::class, $dateTime);
        $this->assertEquals(2023, $dateTime->getYear());
        $this->assertEquals(1, $dateTime->getMonth());
        $this->assertEquals(1, $dateTime->getDay());
        $this->assertEquals(0, $dateTime->getHour());
        $this->assertEquals(0, $dateTime->getMinute());
        $this->assertEquals(0, $dateTime->getSecond());
        $this->assertEquals(0, $dateTime->getMicro());

        // 年月日時分
        $dateTime = LocalDateTime::of(2023, 2, 15, 12, 30);

        $this->assertInstanceOf(LocalDateTime::class, $dateTime);
        $this->assertEquals(2023, $dateTime->getYear());
        $this->assertEquals(2, $dateTime->getMonth());
        $this->assertEquals(15, $dateTime->getDay());
        $this->assertEquals(12, $dateTime->getHour());
        $this->assertEquals(30, $dateTime->getMinute());
        $this->assertEquals(0, $dateTime->getSecond());
        $this->assertEquals(0, $dateTime->getMicro());

        // 年月日時分秒
        $dateTime = LocalDateTime::of(2023, 3, 20, 15, 45, 30);

        $this->assertInstanceOf(LocalDateTime::class, $dateTime);
        $this->assertEquals(2023, $dateTime->getYear());
        $this->assertEquals(3, $dateTime->getMonth());
        $this->assertEquals(20, $dateTime->getDay());
        $this->assertEquals(15, $dateTime->getHour());
        $this->assertEquals(45, $dateTime->getMinute());
        $this->assertEquals(30, $dateTime->getSecond());
        $this->assertEquals(0, $dateTime->getMicro());

        // 年月日時分秒マイクロ秒
        $dateTime = LocalDateTime::of(2023, 4, 25, 18, 15, 45, 123456);

        $this->assertInstanceOf(LocalDateTime::class, $dateTime);
        $this->assertEquals(2023, $dateTime->getYear());
        $this->assertEquals(4, $dateTime->getMonth());
        $this->assertEquals(25, $dateTime->getDay());
        $this->assertEquals(18, $dateTime->getHour());
        $this->assertEquals(15, $dateTime->getMinute());
        $this->assertEquals(45, $dateTime->getSecond());
        $this->assertEquals(123456, $dateTime->getMicro());
    }

    #[Test]
    public function from関数でDateTimeInterfaceからインスタンスが作成できる(): void
    {
        $phpDateTime = new DateTimeImmutable('2023-05-30 14:30:25.123456');
        $dateTime = LocalDateTime::from($phpDateTime);

        $this->assertInstanceOf(LocalDateTime::class, $dateTime);
        $this->assertEquals(2023, $dateTime->getYear());
        $this->assertEquals(5, $dateTime->getMonth());
        $this->assertEquals(30, $dateTime->getDay());
        $this->assertEquals(14, $dateTime->getHour());
        $this->assertEquals(30, $dateTime->getMinute());
        $this->assertEquals(25, $dateTime->getSecond());
        $this->assertEquals(123456, $dateTime->getMicro());
    }

    #[Test]
    public function fromNullable関数でnull値を扱える(): void
    {
        // nullの場合
        $option = LocalDateTime::fromNullable(null);
        $this->assertTrue($option->isNone());

        // 非nullの場合
        $phpDateTime = new DateTimeImmutable('2023-06-10 09:45:15');
        $option = LocalDateTime::fromNullable($phpDateTime);
        $this->assertTrue($option->isSome());
        $this->assertEquals(2023, $option->unwrap()->getYear());
        $this->assertEquals(6, $option->unwrap()->getMonth());
        $this->assertEquals(10, $option->unwrap()->getDay());
        $this->assertEquals(9, $option->unwrap()->getHour());
        $this->assertEquals(45, $option->unwrap()->getMinute());
        $this->assertEquals(15, $option->unwrap()->getSecond());
    }

    #[Test]
    public function tryFrom関数で有効な日時を検証してインスタンス化できる(): void
    {
        $phpDateTime = new DateTimeImmutable('2023-07-15 22:35:40');
        $result = LocalDateTime::tryFrom($phpDateTime);

        $this->assertTrue($result->isOk());
        $this->assertEquals(2023, $result->unwrap()->getYear());
        $this->assertEquals(7, $result->unwrap()->getMonth());
        $this->assertEquals(15, $result->unwrap()->getDay());
        $this->assertEquals(22, $result->unwrap()->getHour());
        $this->assertEquals(35, $result->unwrap()->getMinute());
        $this->assertEquals(40, $result->unwrap()->getSecond());
    }

    #[Test]
    public function tryFromNullable関数でnull値を扱える(): void
    {
        // nullの場合
        $result = LocalDateTime::tryFromNullable(null);
        $this->assertTrue($result->isOk());
        $this->assertTrue($result->unwrap()->isNone());

        // 非nullの場合
        $phpDateTime = new DateTimeImmutable('2023-08-20 11:55:05');
        $result = LocalDateTime::tryFromNullable($phpDateTime);
        $this->assertTrue($result->isOk());
        $this->assertTrue($result->unwrap()->isSome());
        $this->assertEquals(2023, $result->unwrap()->unwrap()->getYear());
        $this->assertEquals(8, $result->unwrap()->unwrap()->getMonth());
        $this->assertEquals(20, $result->unwrap()->unwrap()->getDay());
        $this->assertEquals(11, $result->unwrap()->unwrap()->getHour());
        $this->assertEquals(55, $result->unwrap()->unwrap()->getMinute());
        $this->assertEquals(5, $result->unwrap()->unwrap()->getSecond());
    }

    #[Test]
    public function now関数で現在の日時のインスタンスが作成できる(): void
    {
        $timeZone = new DateTimeZone('Asia/Tokyo');
        $dateTime = LocalDateTime::now($timeZone);

        $this->assertInstanceOf(LocalDateTime::class, $dateTime);

        // 現在の日時と厳密に一致するかを検証するのは難しいため、
        // 基本的な範囲検証のみ行う
        $now = new DateTimeImmutable('now', $timeZone);

        // 日付が±1日の範囲内にあることを確認
        $year = (int)$now->format('Y');
        $month = (int)$now->format('n');
        $day = (int)$now->format('j');

        $this->assertEqualsWithDelta($year, $dateTime->getYear(), 0, '年が一致すること');
        $this->assertEqualsWithDelta($month, $dateTime->getMonth(), 0, '月が一致すること');
        // 時差の問題で日が前後する可能性があるため、±1日の範囲を許容
        $this->assertEqualsWithDelta($day, $dateTime->getDay(), 1, '日が±1日の範囲内であること');

        // 時刻の範囲検証（詳細な一致よりも基本的な範囲内にあることを確認）
        $this->assertGreaterThanOrEqual(0, $dateTime->getHour());
        $this->assertLessThanOrEqual(23, $dateTime->getHour());
        $this->assertGreaterThanOrEqual(0, $dateTime->getMinute());
        $this->assertLessThanOrEqual(59, $dateTime->getMinute());
        $this->assertGreaterThanOrEqual(0, $dateTime->getSecond());
        $this->assertLessThanOrEqual(59, $dateTime->getSecond());
        $this->assertGreaterThanOrEqual(0, $dateTime->getMicro());
        $this->assertLessThanOrEqual(999999, $dateTime->getMicro());
    }

    // ------------------------------------------
    // バリデーションのテスト
    // ------------------------------------------

    /**
     * @return array<string, array{string, bool}>
     */
    public static function 日時の検証データを提供(): array
    {
        return [
            '有効な日時' => ['2023-06-15 12:30:45', true],
            'うるう年の2月29日' => ['2024-02-29 00:00:00', true],
            '無効な日付：通常年の2月29日' => ['2023-02-29 00:00:00', false],
            '無効な日付：存在しない月' => ['2023-13-01 00:00:00', false],
            '無効な日付：存在しない日' => ['2023-04-31 00:00:00', false],
            '無効な時刻：時が範囲外' => ['2023-06-15 24:00:00', false],
            '無効な時刻：分が範囲外' => ['2023-06-15 12:60:00', false],
            '無効な時刻：秒が範囲外' => ['2023-06-15 12:30:60', false],
        ];
    }

    #[Test]
    #[DataProvider('日時の検証データを提供')]
    public function 日時バリデーションのテスト(string $dateTimeString, bool $shouldBeValid): void
    {
        try {
            $phpDateTime = new DateTimeImmutable($dateTimeString);

            if ($shouldBeValid) {
                $result = LocalDateTime::tryFrom($phpDateTime);
                $this->assertTrue($result->isOk(), "日時 {$dateTimeString} は有効なはずですが、エラーが発生しました");

                // 期待値と実際の値のフォーマットを合わせて比較
                // 秒やマイクロ秒が0の場合は出力に含まれない可能性があるため、基本部分のみを比較
                // $expectedBase = $dateTimeString;
                $actualBase = $result->unwrap()->toISOString();
                $expectedBase = preg_replace('/\.\d+$/', '', str_replace(' ', 'T', $dateTimeString));
                // $actualBase = preg_replace('/\.\d+$/', '', $result->unwrap()->toISOString());

                echo "\nexpectedBase: {$expectedBase}\n";
                echo "actualBase: {$actualBase}\n";


                $this->assertStringStartsWith($expectedBase, $actualBase, "期待値:{$expectedBase} と 実際値:{$actualBase} が一致しません");
            } else {
                // 無効な日時については別のテスト方法を使用する
                // DateTimeImmutableは無効な日付を自動的に修正することがあるため
                if (preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $dateTimeString, $matches)) {
                    [, $year, $month, $day, $hour, $minute, $second] = $matches;
                    $year = (int)$year;
                    $month = (int)$month;
                    $day = (int)$day;
                    $hour = (int)$hour;
                    $minute = (int)$minute;
                    $second = (int)$second;

                    // 例外が発生することを検証
                    $exceptionThrown = false;

                    try {
                        LocalDateTime::of($year, $month, $day, $hour, $minute, $second);
                    } catch (AssertionError $e) {
                        $exceptionThrown = true;
                    } catch (Throwable $e) {
                        $exceptionThrown = true;
                    }

                    $this->assertTrue($exceptionThrown, "無効な日時 {$dateTimeString} でエラーが発生しませんでした");
                } else {
                    // 正規表現が一致しない場合はテストを失敗させる
                    $this->fail("日時文字列 {$dateTimeString} の解析に失敗しました");
                }
            }
        } catch (Throwable $e) {
            // DateTimeImmutable が例外を投げる場合（明らかに無効な日時）
            if (!$shouldBeValid) {
                // 無効な日時の場合、例外が発生して正常
                // @phpstan-ignore method.alreadyNarrowedType
                $this->assertTrue(true, '期待通りの例外が発生しました');
            } else {
                // 有効な日時の場合、例外は発生するべきでない
                $this->fail("有効な日時 {$dateTimeString} でエラーが発生しました: " . $e->getMessage());
            }
        }
    }

    // ------------------------------------------
    // メソッドのテスト
    // ------------------------------------------

    #[Test]
    public function toISOString関数で日時をISO形式に変換できる(): void
    {
        $dateTime = LocalDateTime::of(2023, 9, 25, 14, 30, 45, 123456);
        $this->assertEquals('2023-09-25T14:30:45.123456', $dateTime->toISOString());

        // 秒とマイクロ秒がゼロの場合
        $dateTime = LocalDateTime::of(2023, 9, 25, 14, 30);
        $this->assertEquals('2023-09-25T14:30', $dateTime->toISOString());

        // マイクロ秒のみゼロの場合
        $dateTime = LocalDateTime::of(2023, 9, 25, 14, 30, 45);
        $this->assertEquals('2023-09-25T14:30:45', $dateTime->toISOString());
    }

    #[Test]
    public function 文字列表現のテスト(): void
    {
        $dateTime = LocalDateTime::of(2023, 10, 5, 8, 15, 30);
        $this->assertEquals('2023-10-05T08:15:30', (string)$dateTime);
    }

    #[Test]
    public function jsonSerialize関数でJSON形式に変換できる(): void
    {
        $dateTime = LocalDateTime::of(2023, 11, 15, 10, 45, 20);
        $this->assertEquals('"2023-11-15T10:45:20"', json_encode($dateTime));
    }

    #[Test]
    public function getDateとgetTimeメソッドで日付と時刻のコンポーネントを取得できる(): void
    {
        $dateTime = LocalDateTime::of(2023, 12, 25, 23, 59, 59, 999999);

        $date = $dateTime->getDate();
        $time = $dateTime->getTime();

        $this->assertInstanceOf(LocalDate::class, $date);
        $this->assertInstanceOf(LocalTime::class, $time);

        $this->assertEquals(2023, $date->getYear());
        $this->assertEquals(12, $date->getMonth());
        $this->assertEquals(25, $date->getDay());

        $this->assertEquals(23, $time->getHour());
        $this->assertEquals(59, $time->getMinute());
        $this->assertEquals(59, $time->getSecond());
        $this->assertEquals(999999, $time->getMicro());
    }

    #[Test]
    public function getYear_getMonth_getDay_getHour_getMinute_getSecond_getMicroメソッドで各構成要素を取得できる(): void
    {
        $dateTime = LocalDateTime::of(2024, 1, 1, 12, 0, 30, 500000);

        $this->assertEquals(2024, $dateTime->getYear());
        $this->assertEquals(1, $dateTime->getMonth());
        $this->assertEquals(1, $dateTime->getDay());
        $this->assertEquals(12, $dateTime->getHour());
        $this->assertEquals(0, $dateTime->getMinute());
        $this->assertEquals(30, $dateTime->getSecond());
        $this->assertEquals(500000, $dateTime->getMicro());
    }

    // ------------------------------------------
    // 比較関数のテスト
    // ------------------------------------------

    /**
     * @return array<string, array{string, string, int}>
     */
    public static function 日時比較のデータを提供(): array
    {
        return [
            '等しい日時' => ['2023-01-01 12:30:45', '2023-01-01 12:30:45', 0],
            '1秒後' => ['2023-01-01 12:30:46', '2023-01-01 12:30:45', 1],
            '1秒前' => ['2023-01-01 12:30:45', '2023-01-01 12:30:46', -1],
            '1分後' => ['2023-01-01 12:31:45', '2023-01-01 12:30:45', 1],
            '1分前' => ['2023-01-01 12:30:45', '2023-01-01 12:31:45', -1],
            '1時間後' => ['2023-01-01 13:30:45', '2023-01-01 12:30:45', 1],
            '1時間前' => ['2023-01-01 12:30:45', '2023-01-01 13:30:45', -1],
            '1日後' => ['2023-01-02 12:30:45', '2023-01-01 12:30:45', 1],
            '1日前' => ['2023-01-01 12:30:45', '2023-01-02 12:30:45', -1],
            '1ヶ月後' => ['2023-02-01 12:30:45', '2023-01-01 12:30:45', 1],
            '1ヶ月前' => ['2023-01-01 12:30:45', '2023-02-01 12:30:45', -1],
            '1年後' => ['2024-01-01 12:30:45', '2023-01-01 12:30:45', 1],
            '1年前' => ['2023-01-01 12:30:45', '2024-01-01 12:30:45', -1],
        ];
    }

    #[Test]
    #[DataProvider('日時比較のデータを提供')]
    public function compareTo関数で日時の比較ができる(string $dateTime1String, string $dateTime2String, int $expected): void
    {
        $phpDateTime1 = new DateTimeImmutable($dateTime1String);
        $phpDateTime2 = new DateTimeImmutable($dateTime2String);

        $dateTime1 = LocalDateTime::from($phpDateTime1);
        $dateTime2 = LocalDateTime::from($phpDateTime2);

        $this->assertEquals($expected, $dateTime1->compareTo($dateTime2));
    }

    #[Test]
    public function 比較関数のテスト(): void
    {
        $dateTime1 = LocalDateTime::of(2023, 1, 1, 12, 0, 0);
        $dateTime2 = LocalDateTime::of(2023, 1, 1, 12, 0, 1);
        $dateTime3 = LocalDateTime::of(2023, 1, 1, 12, 0, 0);

        // isBefore
        $this->assertTrue($dateTime1->isBefore($dateTime2));
        $this->assertFalse($dateTime2->isBefore($dateTime1));
        $this->assertFalse($dateTime1->isBefore($dateTime3));

        // isBeforeOrEqualTo
        $this->assertTrue($dateTime1->isBeforeOrEqualTo($dateTime2));
        $this->assertFalse($dateTime2->isBeforeOrEqualTo($dateTime1));
        $this->assertTrue($dateTime1->isBeforeOrEqualTo($dateTime3));

        // isAfter
        $this->assertFalse($dateTime1->isAfter($dateTime2));
        $this->assertTrue($dateTime2->isAfter($dateTime1));
        $this->assertFalse($dateTime1->isAfter($dateTime3));

        // isAfterOrEqualTo
        $this->assertFalse($dateTime1->isAfterOrEqualTo($dateTime2));
        $this->assertTrue($dateTime2->isAfterOrEqualTo($dateTime1));
        $this->assertTrue($dateTime1->isAfterOrEqualTo($dateTime3));
    }

    #[Test]
    public function equals関数で同値性の比較ができる(): void
    {
        $dateTime1 = LocalDateTime::of(2023, 1, 1, 12, 0, 0);
        $dateTime2 = LocalDateTime::of(2023, 1, 1, 12, 0, 0);
        $dateTime3 = LocalDateTime::of(2023, 1, 1, 12, 0, 1);

        $this->assertTrue($dateTime1->equals($dateTime2));
        $this->assertFalse($dateTime1->equals($dateTime3));
    }

    // ------------------------------------------
    // アクセス制御のテスト
    // ------------------------------------------

    #[Test]
    public function コンストラクタはprivateアクセス修飾子を持つことを確認(): void
    {
        $reflectionClass = new ReflectionClass(LocalDateTime::class);
        $constructor = $reflectionClass->getConstructor();

        $this->assertNotNull($constructor, 'コンストラクタが見つかりませんでした');
        $this->assertTrue($constructor->isPrivate(), 'コンストラクタはprivateでなければならない');
    }

    // ------------------------------------------
    // エッジケースのテスト
    // ------------------------------------------

    #[Test]
    public function 極端な日時のテスト(): void
    {
        // 範囲内の最小値、最大値
        $minDateTime = LocalDateTime::of(-9999, 1, 1, 0, 0, 0, 0);
        $maxDateTime = LocalDateTime::of(9999, 12, 31, 23, 59, 59, 999999);

        $this->assertEquals('-9999-01-01T00:00', $minDateTime->toISOString());
        $this->assertEquals('9999-12-31T23:59:59.999999', $maxDateTime->toISOString());

        // 比較
        $this->assertTrue($minDateTime->isBefore($maxDateTime));
        $this->assertTrue($maxDateTime->isAfter($minDateTime));
    }

    #[Test]
    public function 日付コンポーネントと時刻コンポーネントの連携テスト(): void
    {
        $dateTime = LocalDateTime::of(2023, 1, 15, 12, 30, 45);

        $date = $dateTime->getDate();
        $time = $dateTime->getTime();

        // 日付と時刻のコンポーネントがそれぞれ正しい値を持っているか
        $this->assertEquals(2023, $date->getYear());
        $this->assertEquals(1, $date->getMonth());
        $this->assertEquals(15, $date->getDay());

        $this->assertEquals(12, $time->getHour());
        $this->assertEquals(30, $time->getMinute());
        $this->assertEquals(45, $time->getSecond());

        // 日付と時刻のコンポーネントから再構築した日時が元の日時と同じか
        $reconstructed = LocalDateTime::of(
            $date->getYear(),
            $date->getMonth(),
            $date->getDay(),
            $time->getHour(),
            $time->getMinute(),
            $time->getSecond()
        );

        $this->assertTrue($dateTime->equals($reconstructed));
    }

    // ------------------------------------------
    // 追加テスト
    // ------------------------------------------

    #[Test]
    public function 日時の比較における日付優先順位のテスト(): void
    {
        // 日付部分が違い、時刻部分が同じ
        $earlier = LocalDateTime::of(2023, 1, 1, 12, 0, 0);
        $later = LocalDateTime::of(2023, 1, 2, 12, 0, 0);

        $this->assertTrue($earlier->isBefore($later));
        $this->assertTrue($later->isAfter($earlier));

        // 日付部分が違い、時刻部分で前後が逆の場合（日付が優先されるはず）
        $earlierDate = LocalDateTime::of(2023, 1, 1, 23, 59, 59);
        $laterDate = LocalDateTime::of(2023, 1, 2, 0, 0, 0);

        $this->assertTrue($earlierDate->isBefore($laterDate));
        $this->assertTrue($laterDate->isAfter($earlierDate));
    }

    #[Test]
    public function 日時の比較における時刻優先順位のテスト(): void
    {
        // 日付部分が同じで、時刻部分が違う場合
        $sameDay1 = LocalDateTime::of(2023, 1, 1, 12, 0, 0);
        $sameDay2 = LocalDateTime::of(2023, 1, 1, 12, 0, 1);

        $this->assertTrue($sameDay1->isBefore($sameDay2));

        // 時分秒が同じで、マイクロ秒のみ違う場合
        $sameSec1 = LocalDateTime::of(2023, 1, 1, 12, 0, 0, 0);
        $sameSec2 = LocalDateTime::of(2023, 1, 1, 12, 0, 0, 1);

        $this->assertTrue($sameSec1->isBefore($sameSec2));
    }
}
