<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\DateTime;

use AssertionError;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use WizDevelop\PhpValueObject\DateTime\LocalDate;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\Tests\TestCase;

/**
 * LocalDateクラスの異常系テスト
 */
#[TestDox('LocalDateクラスの異常系テスト')]
#[Group('DateTime')]
#[CoversClass(LocalDate::class)]
final class LocalDateErrorTest extends TestCase
{
    // ------------------------------------------
    // 範囲外の値によるエラーのテスト
    // ------------------------------------------

    /**
     * @return array<string, array{int}>
     */
    public static function 無効な年のデータを提供(): array
    {
        return [
            '最小値未満の年' => [LocalDate::MIN_YEAR - 1],
            // '最大値を超える年' => [LocalDate::MAX_YEAR + 1], // HACK: DateTimeImmutableインスタンスにすると2000年に変換されるため、テストできない
            '極端に小さい年' => [-10000],
            // '極端に大きい年' => [10000], // HACK: DateTimeImmutableインスタンスにすると2000年に変換されるため、テストできない
        ];
    }

    #[Test]
    #[DataProvider('無効な年のデータを提供')]
    public function 無効な年でインスタンスを作成するとエラーとなる(int $invalidYear): void
    {
        $result = LocalDate::tryFrom(new DateTimeImmutable("{$invalidYear}-01-01"));

        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());

        $error = $result->unwrapErr();
        $this->assertStringContainsString('年', $error->getMessage());
        $this->assertStringContainsString((string)$invalidYear, $error->getMessage());
    }

    /**
     * @return array<string, array{int}>
     */
    public static function 無効な月のデータを提供(): array
    {
        return [
            '0月' => [0],
            '13月' => [13],
            '極端に小さい月' => [-5],
            '極端に大きい月' => [100],
        ];
    }

    #[Test]
    #[DataProvider('無効な月のデータを提供')]
    public function 無効な月でインスタンスを作成するとエラーとなる(int $invalidMonth): void
    {
        // DateTimeImmutableでは無効な月を指定するとエラーになるため、
        // tryFromメソッドを直接テストすることはできないので、
        // このテストは省略します。実際のコードでは月の範囲チェックされています。

        // 代わりに、アサーションで範囲外の月を検証するコードをテストします
        $this->expectException(AssertionError::class);

        // PHPStanのエラーを回避するため
        // @phpstan-ignore-next-line
        LocalDate::of(2023, $invalidMonth, 1);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function 無効な日のデータを提供(): array
    {
        return [
            '0日' => [0],
            '32日' => [32],
            '極端に小さい日' => [-5],
            '極端に大きい日' => [100],
        ];
    }

    #[Test]
    #[DataProvider('無効な日のデータを提供')]
    public function 無効な日でインスタンスを作成するとエラーとなる(int $invalidDay): void
    {
        // DateTimeImmutableでは無効な日を指定するとエラーになるため、
        // tryFromメソッドを直接テストすることはできないので、
        // このテストは省略します。実際のコードでは日の範囲チェックされています。

        // 代わりに、アサーションで範囲外の日を検証するコードをテストします
        $this->expectException(AssertionError::class);

        // PHPStanのエラーを回避するため
        // @phpstan-ignore-next-line
        LocalDate::of(2023, 1, $invalidDay);
    }

    // ------------------------------------------
    // 存在しない日付によるエラーのテスト
    // ------------------------------------------

    /**
     * @return array<string, array{int, int, int}>
     */
    public static function 実在しない日付のデータを提供(): array
    {
        return [
            '2月30日（通常年）' => [2023, 2, 30],
            '2月29日（通常年）' => [2023, 2, 29],
            '4月31日' => [2023, 4, 31],
            '6月31日' => [2023, 6, 31],
            '9月31日' => [2023, 9, 31],
            '11月31日' => [2023, 11, 31],
        ];
    }

    #[Test]
    #[DataProvider('実在しない日付のデータを提供')]
    public function 実在しない日付でインスタンスを作成するとエラーとなる(int $year, int $month, int $day): void
    {
        // DateTimeImmutableでは実在しない日付を指定すると自動的に補正される（例：2月30日→3月2日）ため、
        // LocalDate::fromまたはLocalDate::tryFromでのテストは省略します。

        // DateTimeImmutableの挙動をチェック（参考のため）
        $dateTime = new DateTimeImmutable(sprintf('%04d-%02d-%02d', $year, $month, $day));
        $this->assertNotEquals($day, (int)$dateTime->format('j'));

        // アサーションで実在しない日付を検証するコードをテストします
        $this->expectException(AssertionError::class);

        // PHPStanのエラーを回避するため
        // @phpstan-ignore-next-line
        LocalDate::of($year, $month, $day);
    }

    // ------------------------------------------
    // うるう年関連の日付処理のテスト
    // ------------------------------------------

    #[Test]
    public function うるう年以外の2月29日はエラーとなる(): void
    {
        $this->expectException(AssertionError::class);

        // 2023年はうるう年ではない
        LocalDate::of(2023, 2, 29);
    }

    #[Test]
    public function うるう年の2月29日は正常に作成できる(): void
    {
        // 2024年はうるう年
        $date = LocalDate::of(2024, 2, 29);

        $this->assertSame(2024, $date->getYear());
        $this->assertSame(2, $date->getMonth());
        $this->assertSame(29, $date->getDay());
    }

    // ------------------------------------------
    // addMonthsとうるう年の扱いのテスト
    // ------------------------------------------

    #[Test]
    public function うるう年の2月29日から1年後は2月28日になる(): void
    {
        $leapDate = LocalDate::of(2024, 2, 29);
        $result = $leapDate->addYears(1);

        $this->assertSame(2025, $result->getYear());
        $this->assertSame(2, $result->getMonth());
        $this->assertSame(28, $result->getDay());
    }

    #[Test]
    public function うるう年の2月29日から4年後は2月29日になる(): void
    {
        $leapDate = LocalDate::of(2024, 2, 29);
        $result = $leapDate->addYears(4);

        $this->assertSame(2028, $result->getYear());
        $this->assertSame(2, $result->getMonth());
        $this->assertSame(29, $result->getDay());
    }

    #[Test]
    public function 月末から月を加算すると正しく調整される(): void
    {
        // 1月31日から1ヶ月後は2月28日（通常年）または2月29日（うるう年）
        $date1 = LocalDate::of(2023, 1, 31);
        $result1 = $date1->addMonths(1);
        $this->assertSame(2023, $result1->getYear());
        $this->assertSame(2, $result1->getMonth());
        $this->assertSame(28, $result1->getDay());

        $date2 = LocalDate::of(2024, 1, 31);
        $result2 = $date2->addMonths(1);
        $this->assertSame(2024, $result2->getYear());
        $this->assertSame(2, $result2->getMonth());
        $this->assertSame(29, $result2->getDay());

        // 3月31日から1ヶ月後は4月30日
        $date3 = LocalDate::of(2023, 3, 31);
        $result3 = $date3->addMonths(1);
        $this->assertSame(2023, $result3->getYear());
        $this->assertSame(4, $result3->getMonth());
        $this->assertSame(30, $result3->getDay());
    }

    // ------------------------------------------
    // toEpochDayの境界値テスト
    // ------------------------------------------

    #[Test]
    public function 極端な年のtoEpochDayが正しく動作する(): void
    {
        // 最小年
        $minDate = LocalDate::of(LocalDate::MIN_YEAR, 1, 1);
        $minEpochDay = $minDate->toEpochDay();

        // 最大年
        $maxDate = LocalDate::of(LocalDate::MAX_YEAR, 12, 31);
        $maxEpochDay = $maxDate->toEpochDay();

        // 最小年から最大年の日付を作成できることを確認
        $reconstructedMinDate = LocalDate::ofEpochDay($minEpochDay);
        $reconstructedMaxDate = LocalDate::ofEpochDay($maxEpochDay);

        $this->assertSame(LocalDate::MIN_YEAR, $reconstructedMinDate->getYear());
        $this->assertSame(1, $reconstructedMinDate->getMonth());
        $this->assertSame(1, $reconstructedMinDate->getDay());

        $this->assertSame(LocalDate::MAX_YEAR, $reconstructedMaxDate->getYear());
        $this->assertSame(12, $reconstructedMaxDate->getMonth());
        $this->assertSame(31, $reconstructedMaxDate->getDay());
    }
}
