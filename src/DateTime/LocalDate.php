<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\DateTime;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Override;
use Stringable;
use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\IValueObject;
use WizDevelop\PhpValueObject\Utils;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * @phpstan-type Year int<LocalDate::MIN_YEAR, LocalDate::MAX_YEAR>
 * @phpstan-type Month int<1, 12>
 * @phpstan-type Day int<1, 31>
 *
 * ローカル日付を表す値オブジェクト
 */
#[ValueObjectMeta(name: 'ローカル日付')]
readonly class LocalDate implements IValueObject, Stringable
{
    /**
     * The minimum supported year for instances of `LocalDate`.
     */
    final public const int MIN_YEAR = -9999;

    /**
     * NOTE: 実装クラスでのオーバーライド用メソッド
     */
    protected static function minYear(): int
    {
        return self::MIN_YEAR;
    }

    final public const int MIN_MONTH = 1;

    /**
     * NOTE: 実装クラスでのオーバーライド用メソッド
     *
     * @return int<1,12>
     */
    protected static function minMonth(): int
    {
        return self::MIN_MONTH;
    }

    final public const int MIN_DAY = 1;

    /**
     * NOTE: 実装クラスでのオーバーライド用メソッド
     *
     * @return int<1,31>
     */
    protected static function minDay(): int
    {
        return self::MIN_DAY;
    }

    /**
     * The maximum supported year for instances of `LocalDate`.
     */
    final public const int MAX_YEAR = 9999;

    /**
     * NOTE: 実装クラスでのオーバーライド用メソッド
     */
    protected static function maxYear(): int
    {
        return self::MAX_YEAR;
    }

    final public const int MAX_MONTH = 12;

    /**
     * NOTE: 実装クラスでのオーバーライド用メソッド
     *
     * @return int<1,12>
     */
    protected static function maxMonth(): int
    {
        return self::MAX_MONTH;
    }

    final public const int MAX_DAY = 31;

    /**
     * NOTE: 実装クラスでのオーバーライド用メソッド
     *
     * @return int<1,31>
     */
    protected static function maxDay(): int
    {
        return self::MAX_DAY;
    }

    /**
     * The number of days from year zero to year 1970.
     */
    final public const int DAYS_0000_TO_1970 = 719528;

    /**
     * The number of days in a 400 year cycle.
     */
    final public const int DAYS_PER_CYCLE = 146097;

    /**
     * Avoid new() operator.
     * @param int   $year  the year to represent, validated from MIN_YEAR to MAX_YEAR
     * @param Month $month the month, from 1 to 12
     * @param Day   $day   the day, from 1 to 31
     */
    final private function __construct(
        private int $year,
        private int $month,
        private int $day
    ) {
        // NOTE: 不変条件（invariant）
        Utils::assertResultIsOk(static::isValid($year, $month, $day));
        Utils::assertResultIsOk(static::isValidYear($year));
        Utils::assertResultIsOk(static::isValidMonth($month));
        Utils::assertResultIsOk(static::isValidDay($day));
        Utils::assertResultIsOk(static::isValidDate($year, $month, $day));
    }

    // -------------------------------------------------------------------------
    // MARK: implement IValueObject
    // -------------------------------------------------------------------------
    #[Override]
    final public function equals(IValueObject $other): bool
    {
        return $this->compareTo($other) === 0;
    }

    #[Override]
    final public function __toString(): string
    {
        return $this->toISOString();
    }

    #[Override]
    final public function jsonSerialize(): string
    {
        return (string)$this;
    }

    // -------------------------------------------------------------------------
    // MARK: factory methods
    // -------------------------------------------------------------------------
    /**
     * @param int   $year  the year to represent, validated from MIN_YEAR to MAX_YEAR
     * @param Month $month the month, from 1 to 12
     * @param Day   $day   the day, from 1 to 31
     */
    final public static function of(int $year, int $month, int $day): static
    {
        return new static($year, $month, $day);
    }

    /**
     * Creates a LocalTime from a native DateTime or DateTimeImmutable object.
     */
    final public static function from(DateTimeInterface $value): static
    {
        [$year, $month, $day] = self::extractDate($value);

        return static::of($year, $month, $day);
    }

    /**
     * Creates a LocalTime from a native DateTime or DateTimeImmutable object.
     * @return Option<static>
     */
    final public static function fromNullable(?DateTimeInterface $value): Option
    {
        if ($value === null) {
            return Option\none();
        }

        return Option\some(static::from($value));
    }

    /**
     * @return Result<static,ValueObjectError>
     */
    final public static function tryFrom(DateTimeInterface $value): Result
    {
        [$year, $month, $day] = self::extractDate($value);

        return static::isValid($year, $month, $day)
            ->andThen(static fn () => static::isValidYear($year))
            ->andThen(static fn () => static::isValidMonth($month))
            ->andThen(static fn () => static::isValidDay($day))
            ->andThen(static fn () => static::isValidDate($year, $month, $day))
            ->andThen(static fn () => Result\ok(new static($year, $month, $day)));
    }

    /**
     * @return Result<Option<static>,ValueObjectError>
     */
    final public static function tryFromNullable(?DateTimeInterface $value): Result
    {
        if ($value === null) {
            // @phpstan-ignore return.type
            return Result\ok(Option\none());
        }

        // @phpstan-ignore return.type
        return static::tryFrom($value)->map(static fn ($result) => Option\some($result));
    }

    final public static function now(DateTimeZone $timeZone = new DateTimeZone('Asia/Tokyo')): static
    {
        $value = new DateTimeImmutable('now', $timeZone);

        [$year, $month, $day] = self::extractDate($value);

        return static::of($year, $month, $day);
    }

    final public static function max(): static
    {
        return static::of(static::maxYear(), static::maxMonth(), static::maxDay());
    }

    final public static function min(): static
    {
        return static::of(static::minYear(), static::minMonth(), static::minDay());
    }

    /**
     * Obtains an instance of `LocalDate` from the epoch day count.
     *
     * The Epoch Day count is a simple incrementing count of days
     * where day 0 is 1970-01-01. Negative numbers represent earlier days.
     */
    final public static function ofEpochDay(int $epochDay): static
    {
        $zeroDay = $epochDay + self::DAYS_0000_TO_1970;
        // Find the march-based year.
        $zeroDay -= 60; // Adjust to 0000-03-01 so leap day is at end of four year cycle.
        $adjust = 0;
        if ($zeroDay < 0) {
            // Adjust negative years to positive for calculation.
            $adjustCycles = intdiv($zeroDay + 1, self::DAYS_PER_CYCLE) - 1;
            $adjust = $adjustCycles * 400;
            $zeroDay += -$adjustCycles * self::DAYS_PER_CYCLE;
        }
        $yearEst = intdiv(400 * $zeroDay + 591, self::DAYS_PER_CYCLE);
        $doyEst = $zeroDay - (365 * $yearEst + intdiv($yearEst, 4) - intdiv($yearEst, 100) + intdiv($yearEst, 400));
        if ($doyEst < 0) {
            // Fix estimate.
            --$yearEst;
            $doyEst = $zeroDay - (365 * $yearEst + intdiv($yearEst, 4) - intdiv($yearEst, 100) + intdiv($yearEst, 400));
        }
        $yearEst += $adjust; // Reset any negative year.
        $marchDoy0 = $doyEst;

        // Convert march-based values back to January-based.
        $marchMonth0 = intdiv($marchDoy0 * 5 + 2, 153);

        /** @var Month $month */
        $month = ($marchMonth0 + 2) % 12 + 1;

        /** @var Day $dom */
        $dom = $marchDoy0 - intdiv($marchMonth0 * 306 + 5, 10) + 1;

        $yearEst += intdiv($marchMonth0, 10);

        return new static($yearEst, $month, $dom);
    }

    // -------------------------------------------------------------------------
    // MARK: validation methods
    // -------------------------------------------------------------------------
    /**
     * 有効な年かどうかを判定
     * @return Result<bool,ValueObjectError>
     */
    final protected static function isValidYear(int $year): Result
    {
        $minYear = static::minYear() > self::MIN_YEAR ? static::minYear() : self::MIN_YEAR;
        $maxYear = static::maxYear() < self::MAX_YEAR ? static::maxYear() : self::MAX_YEAR;

        if ($year < $minYear || $year > $maxYear) {
            return Result\err(
                ValueObjectError::dateTime()->invalidRange(
                    className: static::class,
                    attributeName: '年',
                    value: (string)$year,
                    minValue: (string)$minYear,
                    maxValue: (string)$maxYear,
                )
            );
        }

        return Result\ok(true);
    }

    /**
     * 有効な月かどうかを判定
     * @return Result<bool,ValueObjectError>
     */
    final protected static function isValidMonth(int $month): Result
    {
        $minMonth = static::minMonth() > self::MIN_MONTH ? static::minMonth() : self::MIN_MONTH;
        $maxMonth = static::maxMonth() < self::MAX_MONTH ? static::maxMonth() : self::MAX_MONTH;

        if ($month < $minMonth || $month > $maxMonth) {
            return Result\err(
                ValueObjectError::dateTime()->invalidRange(
                    className: static::class,
                    attributeName: '月',
                    value: (string)$month,
                    minValue: (string)$minMonth,
                    maxValue: (string)$maxMonth,
                )
            );
        }

        return Result\ok(true);
    }

    /**
     * 有効な日かどうかを判定
     * @return Result<bool,ValueObjectError>
     */
    final protected static function isValidDay(int $day): Result
    {
        $minDay = static::minDay() > self::MIN_DAY ? static::minDay() : self::MIN_DAY;
        $maxDay = static::maxDay() < self::MAX_DAY ? static::maxDay() : self::MAX_DAY;

        if ($day < $minDay || $day > $maxDay) {
            return Result\err(
                ValueObjectError::dateTime()->invalidRange(
                    className: static::class,
                    attributeName: '日',
                    value: (string)$day,
                    minValue: (string)$minDay,
                    maxValue: (string)$maxDay,
                )
            );
        }

        return Result\ok(true);
    }

    /**
     * 有効な日かどうかを判定
     * @param  int                           $year        年
     * @param  Month                         $monthOfYear 月
     * @param  Day                           $dayOfMonth  日
     * @return Result<bool,ValueObjectError>
     */
    final protected static function isValidDate(int $year, int $monthOfYear, int $dayOfMonth): Result
    {
        $monthLength = self::lengthOfMonth($year, $monthOfYear);

        if ($dayOfMonth > $monthLength) {
            if ($dayOfMonth === 29) {
                return Result\err(
                    ValueObjectError::dateTime()->invalidLeapYear(
                        className: static::class,
                        year: (string)$year,
                        month: (string)$monthOfYear,
                        day: (string)$dayOfMonth,
                    )
                );
            }

            return Result\err(
                ValueObjectError::dateTime()->invalidDate(
                    className: static::class,
                    year: (string)$year,
                    month: (string)$monthOfYear,
                    day: (string)$dayOfMonth,
                )
            );
        }

        return Result\ok(true);
    }

    /**
     * 有効な値かどうか
     * NOTE: 実装クラスでのオーバーライド用メソッド
     * @return Result<bool,ValueObjectError>
     */
    protected static function isValid(int $year, int $month, int $day): Result
    {
        return Result\ok(true);
    }

    // -------------------------------------------------------------------------
    // MARK: public methods
    // -------------------------------------------------------------------------
    /**
     * Returns the ISO 8601 representation of this date.
     *
     * @return non-empty-string
     */
    final public function toISOString(): string
    {
        // This code is optimized for high performance
        return ($this->year < 1000 && $this->year > -1000
            ? (
                $this->year < 0
                    ? '-' . mb_str_pad((string)-$this->year, 4, '0', STR_PAD_LEFT)
                    : mb_str_pad((string)$this->year, 4, '0', STR_PAD_LEFT)
            )
            : $this->year
        )
            . '-'
            . ($this->month < 10 ? '0' . $this->month : $this->month)
            . '-'
            . ($this->day < 10 ? '0' . $this->day : $this->day);
    }

    /**
     * @return Year
     */
    final public function getYear(): int
    {
        // @phpstan-ignore return.type
        return $this->year;
    }

    /**
     * @return Month
     */
    final public function getMonth(): int
    {
        return $this->month;
    }

    /**
     * @return Day
     */
    final public function getDay(): int
    {
        return $this->day;
    }

    /**
     * Returns the length of this month in days.
     *
     * This takes a flag to determine whether to return the length for a leap year or not.
     *
     * February has 28 days in a standard year and 29 days in a leap year.
     * April, June, September and November have 30 days.
     * All other months have 31 days.
     *
     * @return int<28, 31>
     */
    final public function getLengthOfMonth(): int
    {
        return self::lengthOfMonth($this->year, $this->month);
    }

    // -------------------------------------------------------------------------
    // MARK: comparison methods
    // -------------------------------------------------------------------------
    /**
     * Returns -1 if this date is before the given date, 1 if after, 0 if the dates are equal.
     *
     * @return int [-1,0,1] If this date is before, on, or after the given date
     */
    final public function compareTo(self $that): int
    {
        if ($this->year < $that->year) {
            return -1;
        }
        if ($this->year > $that->year) {
            return 1;
        }
        if ($this->month < $that->month) {
            return -1;
        }
        if ($this->month > $that->month) {
            return 1;
        }
        if ($this->day < $that->day) {
            return -1;
        }
        if ($this->day > $that->day) {
            return 1;
        }

        return 0;
    }

    final public function is(self $that): bool
    {
        return $this->compareTo($that) === 0;
    }

    final public function isBefore(self $that): bool
    {
        return $this->compareTo($that) === -1;
    }

    final public function isBeforeOrEqualTo(self $that): bool
    {
        return $this->compareTo($that) <= 0;
    }

    final public function isAfter(self $that): bool
    {
        return $this->compareTo($that) === 1;
    }

    final public function isAfterOrEqualTo(self $that): bool
    {
        return $this->compareTo($that) >= 0;
    }

    // -------------------------------------------------------------------------
    // MARK: arithmetic methods
    // -------------------------------------------------------------------------
    /**
     * Returns a copy of this LocalDate with the specified period in years added.
     *
     * If the day-of-month is invalid for the resulting year and month,
     * it will be changed to the last valid day of the month.
     */
    final public function addYears(int $years): static
    {
        if ($years === 0) {
            return $this;
        }

        return self::resolvePreviousValid($this->year + $years, $this->month, $this->day);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in months added.
     *
     * If the day-of-month is invalid for the resulting year and month,
     * it will be changed to the last valid day of the month.
     */
    final public function addMonths(int $months): static
    {
        if ($months === 0) {
            return $this;
        }

        $month = $this->month + $months - 1;

        $yearDiff = Math::floorDiv($month, 12);

        /** @var Month $month */
        $month = Math::floorMod($month, 12) + 1;

        $year = $this->year + $yearDiff;

        return self::resolvePreviousValid($year, $month, $this->day);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in weeks added.
     */
    final public function addWeeks(int $weeks): static
    {
        if ($weeks === 0) {
            return $this;
        }

        return $this->addDays($weeks * 7);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in days added.
     */
    final public function addDays(int $days): static
    {
        if ($days === 0) {
            return $this;
        }

        // Performance optimization for a common use case.
        if ($days === 1) {
            if ($this->day >= 28 && self::isEndOfMonth($this->year, $this->month, $this->day)) {
                return new static($this->year + intdiv($this->month, 12), ($this->month % 12) + 1, 1);
            }

            // @phpstan-ignore argument.type ($this->day + 1 is not int<2, 32> as PHPStan thinks)
            return new static($this->year, $this->month, $this->day + 1);
        }

        return static::ofEpochDay($this->toEpochDay() + $days);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in years subtracted.
     */
    final public function subYears(int $years): static
    {
        return $this->addYears(-$years);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in months subtracted.
     */
    final public function subMonths(int $months): static
    {
        return $this->addMonths(-$months);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in weeks subtracted.
     */
    final public function subWeeks(int $weeks): static
    {
        return $this->addWeeks(-$weeks);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in days subtracted.
     */
    final public function subDays(int $days): static
    {
        return $this->addDays(-$days);
    }

    // -------------------------------------------------------------------------
    // MARK: conversion methods
    // -------------------------------------------------------------------------
    /**
     * Returns a local date-time formed from this date at the specified time.
     */
    final public function atTime(LocalTime $time): LocalDateTime
    {
        return  LocalDateTime::of($this, $time);
    }

    /**
     * Converts this LocalDate to a native DateTime object.
     *
     * The result is a DateTime with time 00:00 in the UTC time-zone.
     */
    final public function toDateTimeImmutable(): DateTimeImmutable
    {
        return $this->atTime(LocalTime::midnight())->toDateTimeImmutable();
    }

    /**
     * Returns the number of days since the UNIX epoch of 1st January 1970.
     */
    final public function toEpochDay(): int
    {
        $y = $this->year;
        $m = $this->month;

        $total = 365 * $y;

        if ($y >= 0) {
            $total += intdiv($y + 3, 4) - intdiv($y + 99, 100) + intdiv($y + 399, 400);
        } else {
            $total -= intdiv($y, -4) - intdiv($y, -100) + intdiv($y, -400);
        }

        $total += intdiv(367 * $m - 362, 12);
        $total += $this->day - 1;

        if ($m > 2) {
            --$total;
            if (! self::isLeapYear($this->year)) {
                --$total;
            }
        }

        return $total - self::DAYS_0000_TO_1970;
    }

    // -------------------------------------------------------------------------
    // MARK: private methods
    // -------------------------------------------------------------------------
    /**
     * @return array{0:Year, 1:Month, 2:Day}
     */
    private static function extractDate(DateTimeInterface $value): array
    {
        /** @var Year */
        $year = (int)$value->format('Y');

        /** @var Month */
        $month = (int)$value->format('n');

        /** @var Day */
        $day = (int)$value->format('j');

        return [$year, $month, $day];
    }

    /**
     * Resolves the date, resolving days past the end of month.
     *
     * @param int   $year  the year to represent, validated from MIN_YEAR to MAX_YEAR
     * @param Month $month the month-of-year to represent
     * @param Day   $day   the day-of-month to represent, validated from 1 to 31
     */
    private static function resolvePreviousValid(int $year, int $month, int $day): static
    {
        if ($day > 28) {
            $day = min($day, self::lengthOfMonth($year, $month));
        }

        return new static($year, $month, $day);
    }

    /**
     * Returns whether the year is a leap year.
     */
    private static function isLeapYear(int $year): bool
    {
        return (($year & 3) === 0) && (($year % 100) !== 0 || ($year % 400) === 0);
    }

    /**
     * @param  Month       $month
     * @return int<28, 31>
     */
    private static function lengthOfMonth(int $year, int $month): int
    {
        return match ($month) {
            2 => self::isLeapYear($year) ? 29 : 28,
            4, 6, 9, 11 => 30,
            default => 31,
        };
    }

    /**
     * Returns whether this date is the last day of the month.
     * @param Month $month
     * @param Day   $day
     */
    private static function isEndOfMonth(int $year, int $month, int $day): bool
    {
        return $day === self::lengthOfMonth($year, $month);
    }
}
