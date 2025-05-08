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
    public function of関数で有効な時刻のインスタンスが作成できる(): void
    {
        // 時分のみ
        $time = LocalTime::of(12, 30);

        $this->assertInstanceOf(LocalTime::class, $time);
        $this->assertEquals(12, $time->getHour());
        $this->assertEquals(30, $time->getMinute());
        $this->assertEquals(0, $time->getSecond());
        $this->assertEquals(0, $time->getMicro());

        // 時分秒
        $time = LocalTime::of(15, 45, 30);

        $this->assertInstanceOf(LocalTime::class, $time);
        $this->assertEquals(15, $time->getHour());
        $this->assertEquals(45, $time->getMinute());
        $this->assertEquals(30, $time->getSecond());
        $this->assertEquals(0, $time->getMicro());

        // 時分秒マイクロ秒
        $time = LocalTime::of(18, 15, 45, 123456);

        $this->assertInstanceOf(LocalTime::class, $time);
        $this->assertEquals(18, $time->getHour());
        $this->assertEquals(15, $time->getMinute());
        $this->assertEquals(45, $time->getSecond());
        $this->assertEquals(123456, $time->getMicro());
    }

    #[Test]
    public function from関数でDateTimeInterfaceからインスタンスが作成できる(): void
    {
        $dateTime = new DateTimeImmutable('2023-02-15 14:30:25.123456');
        $time = LocalTime::from($dateTime);

        $this->assertInstanceOf(LocalTime::class, $time);
        $this->assertEquals(14, $time->getHour());
        $this->assertEquals(30, $time->getMinute());
        $this->assertEquals(25, $time->getSecond());
        $this->assertEquals(123456, $time->getMicro());
    }

    #[Test]
    public function fromNullable関数でnull値を扱える(): void
    {
        // nullの場合
        $option = LocalTime::fromNullable(null);
        $this->assertTrue($option->isNone());

        // 非nullの場合
        $dateTime = new DateTimeImmutable('2023-03-20 09:45:15');
        $option = LocalTime::fromNullable($dateTime);
        $this->assertTrue($option->isSome());
        $this->assertEquals(9, $option->unwrap()->getHour());
        $this->assertEquals(45, $option->unwrap()->getMinute());
        $this->assertEquals(15, $option->unwrap()->getSecond());
    }

    #[Test]
    public function tryFrom関数で有効な時刻を検証してインスタンス化できる(): void
    {
        $dateTime = new DateTimeImmutable('2023-04-25 22:35:40');
        $result = LocalTime::tryFrom($dateTime);

        $this->assertTrue($result->isOk());
        $this->assertEquals(22, $result->unwrap()->getHour());
        $this->assertEquals(35, $result->unwrap()->getMinute());
        $this->assertEquals(40, $result->unwrap()->getSecond());
    }

    #[Test]
    public function tryFromNullable関数でnull値を扱える(): void
    {
        // nullの場合
        $result = LocalTime::tryFromNullable(null);
        $this->assertTrue($result->isOk());
        $this->assertTrue($result->unwrap()->isNone());

        // 非nullの場合
        $dateTime = new DateTimeImmutable('2023-05-30 11:55:05');
        $result = LocalTime::tryFromNullable($dateTime);
        $this->assertTrue($result->isOk());
        $this->assertTrue($result->unwrap()->isSome());
        $this->assertEquals(11, $result->unwrap()->unwrap()->getHour());
        $this->assertEquals(55, $result->unwrap()->unwrap()->getMinute());
        $this->assertEquals(5, $result->unwrap()->unwrap()->getSecond());
    }

    #[Test]
    public function now関数で現在の時刻のインスタンスが作成できる(): void
    {
        $timeZone = new DateTimeZone('Asia/Tokyo');
        $time = LocalTime::now($timeZone);

        $this->assertInstanceOf(LocalTime::class, $time);

        // 現在の時刻と厳密に一致するかを検証するのは難しいため、
        // 基本的な範囲検証のみ行う
        $this->assertGreaterThanOrEqual(0, $time->getHour());
        $this->assertLessThanOrEqual(23, $time->getHour());
        $this->assertGreaterThanOrEqual(0, $time->getMinute());
        $this->assertLessThanOrEqual(59, $time->getMinute());
        $this->assertGreaterThanOrEqual(0, $time->getSecond());
        $this->assertLessThanOrEqual(59, $time->getSecond());
        $this->assertGreaterThanOrEqual(0, $time->getMicro());
        $this->assertLessThanOrEqual(999999, $time->getMicro());
    }

    // ------------------------------------------
    // バリデーションのテスト
    // ------------------------------------------

    /**
     * @return array<string, array{int, int, int, int, bool}>
     */
    public static function 時刻の検証データを提供(): array
    {
        return [
            '有効な時刻' => [12, 30, 45, 123456, true],
            '0時0分0秒0マイクロ秒' => [0, 0, 0, 0, true],
            '23時59分59秒999999マイクロ秒' => [23, 59, 59, 999999, true],
            '無効な時刻：時が範囲外' => [24, 30, 45, 0, false],
            '無効な時刻：分が範囲外' => [12, 60, 45, 0, false],
            '無効な時刻：秒が範囲外' => [12, 30, 60, 0, false],
            '無効な時刻：マイクロ秒が範囲外' => [12, 30, 45, 1000000, false],
            '無効な時刻：負の時' => [-1, 30, 45, 0, false],
            '無効な時刻：負の分' => [12, -1, 45, 0, false],
            '無効な時刻：負の秒' => [12, 30, -1, 0, false],
            '無効な時刻：負のマイクロ秒' => [12, 30, 45, -1, false],
        ];
    }

    #[Test]
    #[DataProvider('時刻の検証データを提供')]
    public function 時刻バリデーションのテスト(int $hour, int $minute, int $second, int $micro, bool $shouldBeValid): void
    {
        if ($shouldBeValid) {
            try {
                $time = LocalTime::of($hour, $minute, $second, $micro);
                $this->assertInstanceOf(LocalTime::class, $time);
                $this->assertEquals($hour, $time->getHour());
                $this->assertEquals($minute, $time->getMinute());
                $this->assertEquals($second, $time->getSecond());
                $this->assertEquals($micro, $time->getMicro());
            } catch (Throwable $e) {
                $this->fail('有効な時刻でエラーが発生: ' . $e->getMessage());
            }
        } else {
            // 無効な値はエラーになるはず
            try {
                LocalTime::of($hour, $minute, $second, $micro);
                $this->fail('無効な時刻であるにもかかわらずエラーが発生しなかった');
            } catch (Throwable $e) {
                // 期待通りの例外
                $this->assertTrue(true, '期待通りのエラーが発生');
            }
        }
    }

    #[Test]
    public function 時のバリデーションテスト(): void
    {
        // 有効な時
        $this->assertInstanceOf(LocalTime::class, LocalTime::of(0, 0)); // 最小値
        $this->assertInstanceOf(LocalTime::class, LocalTime::of(23, 0)); // 最大値

        // 無効な時
        $this->expectException(AssertionError::class);
        LocalTime::of(24, 0); // 最大値超過
    }

    #[Test]
    public function 分のバリデーションテスト(): void
    {
        // 有効な分
        $this->assertInstanceOf(LocalTime::class, LocalTime::of(0, 0)); // 最小値
        $this->assertInstanceOf(LocalTime::class, LocalTime::of(0, 59)); // 最大値

        // 無効な分
        $this->expectException(AssertionError::class);
        LocalTime::of(0, 60); // 最大値超過
    }

    #[Test]
    public function 秒のバリデーションテスト(): void
    {
        // 有効な秒
        $this->assertInstanceOf(LocalTime::class, LocalTime::of(0, 0, 0)); // 最小値
        $this->assertInstanceOf(LocalTime::class, LocalTime::of(0, 0, 59)); // 最大値

        // 無効な秒
        $this->expectException(AssertionError::class);
        LocalTime::of(0, 0, 60); // 最大値超過
    }

    #[Test]
    public function マイクロ秒のバリデーションテスト(): void
    {
        // 有効なマイクロ秒
        $this->assertInstanceOf(LocalTime::class, LocalTime::of(0, 0, 0, 0)); // 最小値
        $this->assertInstanceOf(LocalTime::class, LocalTime::of(0, 0, 0, 999999)); // 最大値

        // 無効なマイクロ秒
        $this->expectException(AssertionError::class);
        LocalTime::of(0, 0, 0, 1000000); // 最大値超過
    }

    // ------------------------------------------
    // メソッドのテスト
    // ------------------------------------------

    /**
     * @return array<string, array{int, int, int, int, string}>
     */
    public static function toISOString時刻形式のデータを提供(): array
    {
        return [
            '時分のみ' => [12, 30, 0, 0, '12:30'],
            '時分秒' => [15, 45, 30, 0, '15:45:30'],
            '時分秒マイクロ秒' => [18, 15, 45, 123456, '18:15:45.123456'],
            '1桁の時' => [9, 5, 0, 0, '09:05'],
            '1桁の分' => [10, 5, 0, 0, '10:05'],
            '1桁の秒' => [10, 10, 5, 0, '10:10:05'],
            '1桁のマイクロ秒' => [10, 10, 10, 1, '10:10:10.000001'],
        ];
    }

    #[Test]
    #[DataProvider('toISOString時刻形式のデータを提供')]
    public function toISOString関数で時刻をISO形式に変換できる(int $hour, int $minute, int $second, int $micro, string $expected): void
    {
        $time = LocalTime::of($hour, $minute, $second, $micro);
        $this->assertEquals($expected, $time->toISOString());
    }

    #[Test]
    public function 文字列表現のテスト(): void
    {
        $time = LocalTime::of(14, 25, 30);
        $this->assertEquals('14:25:30', (string)$time);
    }

    #[Test]
    public function jsonSerialize関数でJSON形式に変換できる(): void
    {
        $time = LocalTime::of(19, 45, 15);
        $this->assertEquals('"19:45:15"', json_encode($time));
    }

    #[Test]
    public function getHour_getMinute_getSecond_getMicro関数で各構成要素を取得できる(): void
    {
        $time = LocalTime::of(20, 35, 45, 123456);

        $this->assertEquals(20, $time->getHour());
        $this->assertEquals(35, $time->getMinute());
        $this->assertEquals(45, $time->getSecond());
        $this->assertEquals(123456, $time->getMicro());
    }

    #[Test]
    public function toSecondOfDay関数で秒単位の値に変換できる(): void
    {
        // 00:00:00 = 0秒
        $time = LocalTime::of(0, 0, 0);
        $this->assertEquals(0, $time->toSecondOfDay());

        // 01:00:00 = 3600秒
        $time = LocalTime::of(1, 0, 0);
        $this->assertEquals(3600, $time->toSecondOfDay());

        // 00:01:00 = 60秒
        $time = LocalTime::of(0, 1, 0);
        $this->assertEquals(60, $time->toSecondOfDay());

        // 00:00:01 = 1秒
        $time = LocalTime::of(0, 0, 1);
        $this->assertEquals(1, $time->toSecondOfDay());

        // 12:34:56 = 45296秒
        $time = LocalTime::of(12, 34, 56);
        $this->assertEquals(45296, $time->toSecondOfDay());

        // 23:59:59 = 86399秒（1日の最後の秒）
        $time = LocalTime::of(23, 59, 59);
        $this->assertEquals(86399, $time->toSecondOfDay());
    }

    // ------------------------------------------
    // 比較関数のテスト
    // ------------------------------------------

    /**
     * @return array<string, array{string, string, int}>
     */
    public static function 時刻比較のデータを提供(): array
    {
        return [
            '等しい時刻' => ['12:30:45', '12:30:45', 0],
            '1秒後' => ['12:30:46', '12:30:45', 1],
            '1秒前' => ['12:30:45', '12:30:46', -1],
            '1分後' => ['12:31:45', '12:30:45', 1],
            '1分前' => ['12:30:45', '12:31:45', -1],
            '1時間後' => ['13:30:45', '12:30:45', 1],
            '1時間前' => ['12:30:45', '13:30:45', -1],
            'マイクロ秒の差：後' => ['12:30:45.000001', '12:30:45.000000', 1],
            'マイクロ秒の差：前' => ['12:30:45.000000', '12:30:45.000001', -1],
        ];
    }

    #[Test]
    #[DataProvider('時刻比較のデータを提供')]
    public function compareTo関数で時刻の比較ができる(string $time1String, string $time2String, int $expected): void
    {
        // DateTimeImmutableを使用して時刻部分を抽出
        $dateTime1 = new DateTimeImmutable('2023-01-01 ' . $time1String);
        $dateTime2 = new DateTimeImmutable('2023-01-01 ' . $time2String);

        $time1 = LocalTime::from($dateTime1);
        $time2 = LocalTime::from($dateTime2);

        $this->assertEquals($expected, $time1->compareTo($time2));
    }

    #[Test]
    public function 比較関数のテスト(): void
    {
        $time1 = LocalTime::of(12, 30, 45);
        $time2 = LocalTime::of(12, 30, 46);
        $time3 = LocalTime::of(12, 30, 45);

        // isBefore
        $this->assertTrue($time1->isBefore($time2));
        $this->assertFalse($time2->isBefore($time1));
        $this->assertFalse($time1->isBefore($time3));

        // isBeforeOrEqualTo
        $this->assertTrue($time1->isBeforeOrEqualTo($time2));
        $this->assertFalse($time2->isBeforeOrEqualTo($time1));
        $this->assertTrue($time1->isBeforeOrEqualTo($time3));

        // isAfter
        $this->assertFalse($time1->isAfter($time2));
        $this->assertTrue($time2->isAfter($time1));
        $this->assertFalse($time1->isAfter($time3));

        // isAfterOrEqualTo
        $this->assertFalse($time1->isAfterOrEqualTo($time2));
        $this->assertTrue($time2->isAfterOrEqualTo($time1));
        $this->assertTrue($time1->isAfterOrEqualTo($time3));
    }

    #[Test]
    public function equals関数で同値性の比較ができる(): void
    {
        $time1 = LocalTime::of(12, 30, 45);
        $time2 = LocalTime::of(12, 30, 45);
        $time3 = LocalTime::of(12, 30, 46);

        $this->assertTrue($time1->equals($time2));
        $this->assertFalse($time1->equals($time3));
    }

    // ------------------------------------------
    // アクセス制御のテスト
    // ------------------------------------------

    #[Test]
    public function コンストラクタはprivateアクセス修飾子を持つことを確認(): void
    {
        $reflectionClass = new ReflectionClass(LocalTime::class);
        $constructor = $reflectionClass->getConstructor();

        $this->assertNotNull($constructor, 'コンストラクタが見つかりませんでした');
        $this->assertTrue($constructor->isPrivate(), 'コンストラクタはprivateでなければならない');
    }

    // ------------------------------------------
    // エッジケースのテスト
    // ------------------------------------------

    #[Test]
    public function 極端な時刻のテスト(): void
    {
        // 範囲内の最小値、最大値
        $minTime = LocalTime::of(0, 0, 0, 0);
        $maxTime = LocalTime::of(23, 59, 59, 999999);

        $this->assertEquals('00:00', $minTime->toISOString());
        $this->assertEquals('23:59:59.999999', $maxTime->toISOString());

        // 比較
        $this->assertTrue($minTime->isBefore($maxTime));
        $this->assertTrue($maxTime->isAfter($minTime));
    }
}
