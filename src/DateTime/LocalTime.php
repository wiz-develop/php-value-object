<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\DateTime;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Override;
use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\IValueObject;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * ローカル時刻を表す値オブジェクト
 */
#[ValueObjectMeta(displayName: 'ローカル時刻')]
readonly class LocalTime implements IValueObject
{
    final public const int MONTHS_PER_YEAR = 12;
    final public const int DAYS_PER_WEEK = 7;
    final public const int HOURS_PER_DAY = 24;
    final public const int MINUTES_PER_HOUR = 60;
    final public const int MINUTES_PER_DAY = 1440;
    final public const int SECONDS_PER_MINUTE = 60;
    final public const int SECONDS_PER_HOUR = 3600;
    final public const int SECONDS_PER_DAY = 86400;
    final public const int MICROS_PER_SECOND = 1_000_000;

    /**
     * Avoid new() operator.
     * @param int<0,23>     $hour   the hour, from 0 to 23
     * @param int<0,59>     $minute the minute, from 0 to 59
     * @param int<0,59>     $second the second, from 0 to 59
     * @param int<0,999999> $micro  the micro-of-second, from 0 to 999,999
     */
    final private function __construct(
        private int $hour,
        private int $minute,
        private int $second,
        private int $micro
    ) {
        // NOTE: 不変条件（invariant）
        assert(static::isValid($hour, $minute, $second, $micro)->isOk());
        assert(static::isValidHour($hour)->isOk());
        assert(static::isValidMinute($minute)->isOk());
        assert(static::isValidSecond($second)->isOk());
        assert(static::isValidMicro($micro)->isOk());
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
     * @param int<0, 23>     $hour   the hour, from 0 to 23
     * @param int<0, 59>     $minute the minute, from 0 to 59
     * @param int<0, 59>     $second the second, from 0 to 59
     * @param int<0, 999999> $micro  the micro-of-second, from 0 to 999,999
     */
    final public static function of(int $hour, int $minute, int $second = 0, int $micro = 0): static
    {
        return new static($hour, $minute, $second, $micro);
    }

    /**
     * Creates a LocalTime instance from a number of seconds since midnight.
     *
     * @param int<0, 86399>  $secondOfDay   the second-of-day, from 0 to 86,399
     * @param int<0, 999999> $microOfSecond the micro-of-second, from 0 to 999,999
     */
    final public static function ofSecondOfDay(int $secondOfDay, int $microOfSecond = 0): static
    {
        /** @var int<0, 23> */
        $hours = intdiv($secondOfDay, self::SECONDS_PER_HOUR);

        /** @var int<0, 3599> */
        $remainingSeconds = $secondOfDay - ($hours * self::SECONDS_PER_HOUR);

        /** @var int<0, 59> */
        $minutes = intdiv($remainingSeconds, self::SECONDS_PER_MINUTE);

        /** @var int<0, 59> */
        $seconds = $remainingSeconds - ($minutes * self::SECONDS_PER_MINUTE);

        return new static($hours, $minutes, $seconds, $microOfSecond);
    }

    /**
     * Creates a LocalTime from a native DateTime or DateTimeImmutable object.
     */
    final public static function from(DateTimeInterface $value): static
    {
        [$hour, $minute, $second, $micro] = self::extractTime($value);

        return static::of($hour, $minute, $second, $micro);
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
        [$hour, $minute, $second, $micro] = self::extractTime($value);

        return static::isValid($hour, $minute, $second, $micro)
            ->andThen(static fn () => static::isValidHour($hour))
            ->andThen(static fn () => static::isValidMinute($minute))
            ->andThen(static fn () => static::isValidSecond($second))
            ->andThen(static fn () => static::isValidMicro($micro))
            ->andThen(static fn () => Result\ok(static::from($value)));
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

    final public static function now(DateTimeZone $timeZone): static
    {
        $value = new DateTimeImmutable('now', $timeZone);

        [$hour, $minute, $second, $micro] = self::extractTime($value);

        return static::of($hour, $minute, $second, $micro);
    }

    final public static function midnight(): self
    {
        return static::min();
    }

    /**
     * Returns the smallest possible value for LocalTime.
     */
    final public static function min(): self
    {
        return new self(0, 0, 0, 0);
    }

    // -------------------------------------------------------------------------
    // MARK: validation methods
    // -------------------------------------------------------------------------
    /**
     * 有効な時刻かどうかを判定
     * @return Result<bool,ValueObjectError>
     */
    final protected static function isValidHour(int $value): Result
    {
        if (!($value >= 0 && $value <= 23)) {
            return Result\err(ValueObjectError::dateTime()->invalidRange(
                className: static::class,
                attributeName: '時',
                value: (string)$value,
                minValue: '0',
                maxValue: '23'
            ));
        }

        return Result\ok(true);
    }

    /**
     * 有効な分かどうかを判定
     * @return Result<bool,ValueObjectError>
     */
    final protected static function isValidMinute(int $value): Result
    {
        if (!($value >= 0 && $value <= 59)) {
            return Result\err(ValueObjectError::dateTime()->invalidRange(
                className: static::class,
                attributeName: '分',
                value: (string)$value,
                minValue: '0',
                maxValue: '59'
            ));
        }

        return Result\ok(true);
    }

    /**
     * 有効な秒かどうかを判定
     * @return Result<bool,ValueObjectError>
     */
    final protected static function isValidSecond(int $value): Result
    {
        if (!($value >= 0 && $value <= 59)) {
            return Result\err(ValueObjectError::dateTime()->invalidRange(
                className: static::class,
                attributeName: '秒',
                value: (string)$value,
                minValue: '0',
                maxValue: '59'
            ));
        }

        return Result\ok(true);
    }

    /**
     * 有効なマイクロ秒かどうかを判定
     * @return Result<bool,ValueObjectError>
     */
    final protected static function isValidMicro(int $value): Result
    {
        if (!($value >= 0 && $value <= 999999)) {
            return Result\err(ValueObjectError::dateTime()->invalidRange(
                className: static::class,
                attributeName: 'マイクロ秒',
                value: (string)$value,
                minValue: '0',
                maxValue: '999999'
            ));
        }

        return Result\ok(true);
    }

    /**
     * 有効な値かどうか
     * NOTE: 実装クラスでのオーバーライド用メソッド
     * @return Result<bool,ValueObjectError>
     */
    protected static function isValid(int $hour, int $minute, int $second, int $micro): Result
    {
        return Result\ok(true);
    }

    // -------------------------------------------------------------------------
    // MARK: public methods
    // -------------------------------------------------------------------------

    /**
     * Returns the ISO 8601 representation of this time.
     *
     * The output will be one of the following formats:
     *
     * * `HH:mm`
     * * `HH:mm:ss`
     * * `HH:mm:ss.nnn`
     *
     * The format used will be the shortest that outputs the full value of
     * the time where the omitted parts are implied to be zero.
     * The microseconds value, if present, can be 0 to 6 digits.
     *
     * @return non-empty-string
     */
    final public function toISOString(): string
    {
        // This code is optimized for high performance
        return ($this->hour < 10 ? '0' . $this->hour : $this->hour)
            . ':'
            . ($this->minute < 10 ? '0' . $this->minute : $this->minute)
            . ($this->second !== 0 || $this->micro !== 0 ? ':' . ($this->second < 10 ? '0' . $this->second : $this->second) : '')
            . ($this->micro !== 0 ? '.' . mb_rtrim(mb_str_pad((string)$this->micro, 6, '0', STR_PAD_LEFT), '0') : '');
    }

    /**
     * @return int<0,23>
     */
    final public function getHour(): int
    {
        return $this->hour;
    }

    /**
     * @return int<0,59>
     */
    final public function getMinute(): int
    {
        return $this->minute;
    }

    /**
     * @return int<0,59>
     */
    final public function getSecond(): int
    {
        return $this->second;
    }

    /**
     * @return int<0,999999>
     */
    final public function getMicro(): int
    {
        return $this->micro;
    }

    /**
     * Returns the time as seconds of day, from 0 to 24 * 60 * 60 - 1.
     *
     * This does not include the microseconds.
     */
    final public function toSecondOfDay(): int
    {
        return $this->hour * 3600
            + $this->minute * 60
            + $this->second;
    }

    // -------------------------------------------------------------------------
    // MARK: comparison methods
    // -------------------------------------------------------------------------
    /**
     * Compares this LocalTime with another.
     *
     * @param LocalTime $that the time to compare to
     *
     * @return int [-1,0,1] If this time is before, on, or after the given time
     */
    final public function compareTo(self $that): int
    {
        $seconds = $this->toSecondOfDay() - $that->toSecondOfDay();

        if ($seconds !== 0) {
            return $seconds > 0 ? 1 : -1;
        }

        $micros = $this->micro - $that->micro;

        if ($micros !== 0) {
            return $micros > 0 ? 1 : -1;
        }

        return 0;
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
     * Returns a copy of this LocalTime with the specified period in hours added.
     *
     * This adds the specified number of hours to this time, returning a new time.
     * The calculation wraps around midnight.
     *
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $hours the hours to add, may be negative
     *
     * @return static a LocalTime based on this time with the hours added
     */
    final public function addHours(int $hours): static
    {
        if ($hours === 0) {
            return $this;
        }

        $hour = (($hours % self::HOURS_PER_DAY) + $this->hour + self::HOURS_PER_DAY) % self::HOURS_PER_DAY;

        return new static($hour, $this->minute, $this->second, $this->micro);
    }

    /**
     * Returns a copy of this LocalTime with the specified period in minutes added.
     *
     * This adds the specified number of minutes to this time, returning a new time.
     * The calculation wraps around midnight.
     *
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $minutes the minutes to add, may be negative
     *
     * @return static a LocalTime based on this time with the minutes added
     */
    final public function addMinutes(int $minutes): static
    {
        if ($minutes === 0) {
            return $this;
        }

        $mofd = $this->hour * self::MINUTES_PER_HOUR + $this->minute;
        $newMofd = (($minutes % self::MINUTES_PER_DAY) + $mofd + self::MINUTES_PER_DAY) % self::MINUTES_PER_DAY;

        if ($mofd === $newMofd) {
            return $this;
        }

        /** @var int<0, 23> */
        $hour = intdiv($newMofd, self::MINUTES_PER_HOUR);
        $minute = $newMofd % self::MINUTES_PER_HOUR;

        return new static($hour, $minute, $this->second, $this->micro);
    }

    /**
     * Returns a copy of this LocalTime with the specified period in seconds added.
     *
     * @param int $seconds the seconds to add, may be negative
     *
     * @return static a LocalTime based on this time with the seconds added
     */
    final public function addSeconds(int $seconds): static
    {
        if ($seconds === 0) {
            return $this;
        }

        $sofd = $this->hour * self::SECONDS_PER_HOUR + $this->minute * self::SECONDS_PER_MINUTE + $this->second;
        $newSofd = (($seconds % self::SECONDS_PER_DAY) + $sofd + self::SECONDS_PER_DAY) % self::SECONDS_PER_DAY;

        if ($sofd === $newSofd) {
            return $this;
        }

        /** @var int<0, 23> */
        $hour = intdiv($newSofd, self::SECONDS_PER_HOUR);

        /** @var int<0, 59> */
        $minute = intdiv($newSofd, self::SECONDS_PER_MINUTE) % self::MINUTES_PER_HOUR;
        $second = $newSofd % self::SECONDS_PER_MINUTE;

        return new static($hour, $minute, $second, $this->micro);
    }

    /**
     * Returns a copy of this LocalTime with the specified period in microseconds added.
     *
     * @param int $micros the microseconds to add, may be negative
     *
     * @return static a LocalTime based on this time with the microseconds added
     */
    final public function addMicros(int $micros): static
    {
        if ($micros === 0) {
            return $this;
        }

        $divBase = intdiv($this->micro, self::MICROS_PER_SECOND);
        $modBase = $this->micro % self::MICROS_PER_SECOND;

        // $microsは負の可能性があるので調整が必要
        $divPlus = ($micros < 0)
            ? intdiv($micros - self::MICROS_PER_SECOND + 1, self::MICROS_PER_SECOND)
            : intdiv($micros, self::MICROS_PER_SECOND);

        $modPlus = ($micros % self::MICROS_PER_SECOND + self::MICROS_PER_SECOND) % self::MICROS_PER_SECOND;

        $diffSeconds = $divBase + $divPlus;
        $micro = $modBase + $modPlus;

        if ($micro >= self::MICROS_PER_SECOND) {
            $micro -= self::MICROS_PER_SECOND;
            ++$diffSeconds;
        }

        return new static($this->hour, $this->minute, $this->second, $micro)->addSeconds($diffSeconds);
    }

    final public function subHours(int $hours): static
    {
        return $this->addHours(-$hours);
    }

    final public function subMinutes(int $minutes): static
    {
        return $this->addMinutes(-$minutes);
    }

    final public function subSeconds(int $seconds): static
    {
        return $this->addSeconds(-$seconds);
    }

    final public function subMicros(int $micros): static
    {
        return $this->addMicros(-$micros);
    }

    // -------------------------------------------------------------------------
    // MARK: conversion methods
    // -------------------------------------------------------------------------
    /**
     * Combines this time with a date to create a LocalDateTime.
     */
    final public function atDate(LocalDate $date): LocalDateTime
    {
        return LocalDateTime::of($date, $this);
    }

    /**
     * Converts this LocalTime to a native DateTimeImmutable object.
     *
     * The result is a DateTimeImmutable with date 0000-01-01 in the UTC time-zone.
     *
     * Note that the native DateTimeImmutable object supports a precision up to the microsecond,
     */
    final public function toDateTimeImmutable(): DateTimeImmutable
    {
        return $this->atDate(LocalDate::of(0, 1, 1))->toDateTimeImmutable();
    }

    // -------------------------------------------------------------------------
    // MARK: private methods
    // -------------------------------------------------------------------------
    /**
     * @return array{0:int<0,23>, 1:int<0,59>, 2:int<0,59>, 3:int<0,999999>}
     */
    private static function extractTime(DateTimeInterface $value): array
    {
        /** @var int<0,23> */
        $hour = (int)$value->format('G');

        /** @var int<0,59> */
        $minute = (int)$value->format('i');

        /** @var int<0,59> */
        $second = (int)$value->format('s');

        /** @var int<0,999999> */
        $micro = (int)$value->format('u'); // @phpstan-ignore varTag.type

        return [$hour, $minute, $second, $micro];
    }
}
