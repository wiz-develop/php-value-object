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
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * ローカル日時を表す値オブジェクト
 */
#[ValueObjectMeta(name: 'ローカル日時')]
readonly class LocalDateTime implements IValueObject, Stringable
{
    /**
     * Avoid new() operator.
     */
    final private function __construct(
        private LocalDate $date,
        private LocalTime $time,
    ) {
        // NOTE: 不変条件（invariant）
        assert(static::isValid($date, $time)->isOk());
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
    public static function of(LocalDate $date, LocalTime $time): static
    {
        return new static($date, $time);
    }

    /**
     * Creates a LocalTime from a native DateTime or DateTimeImmutable object.
     */
    final public static function from(DateTimeInterface $value): static
    {
        $date = LocalDate::from($value);
        $time = LocalTime::from($value);

        return static::of($date, $time);
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
        return LocalDate::tryFrom($value)
            ->andThen(
                static fn (LocalDate $date) => LocalTime::tryFrom($value)
                    ->andThen(
                        static fn (LocalTime $time) => static::isValid($date, $time)
                            ->andThen(static fn () => Result\ok(static::of($date, $time)))
                    )
            );
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

        $date = LocalDate::from($value);
        $time = LocalTime::from($value);

        return static::of($date, $time);
    }

    // -------------------------------------------------------------------------
    // MARK: validation methods
    // -------------------------------------------------------------------------
    /**
     * 有効な値かどうか
     * NOTE: 実装クラスでのオーバーライド用メソッド
     * @return Result<bool,ValueObjectError>
     */
    protected static function isValid(LocalDate $date, LocalTime $time): Result
    {
        return Result\ok(true);
    }

    // -------------------------------------------------------------------------
    // MARK: public methods
    // -------------------------------------------------------------------------
    /**
     * Returns the ISO 8601 representation of this date time.
     *
     * @return non-empty-string
     */
    final public function toISOString(): string
    {
        return $this->date . 'T' . $this->time;
    }

    final public function getDate(): LocalDate
    {
        return $this->date;
    }

    final public function getTime(): LocalTime
    {
        return $this->time;
    }

    /**
     * @return int<-9999, 9999>
     */
    final public function getYear(): int
    {
        return $this->date->getYear();
    }

    /**
     * @return int<1, 12>
     */
    final public function getMonth(): int
    {
        return $this->date->getMonth();
    }

    /**
     * @return int<1, 31>
     */
    final public function getDay(): int
    {
        return $this->date->getDay();
    }

    /**
     * @return int<0, 23>
     */
    final public function getHour(): int
    {
        return $this->time->getHour();
    }

    /**
     * @return int<0, 59>
     */
    final public function getMinute(): int
    {
        return $this->time->getMinute();
    }

    /**
     * @return int<0, 59>
     */
    final public function getSecond(): int
    {
        return $this->time->getSecond();
    }

    /**
     * @return int<0, 999999>
     */
    final public function getMicro(): int
    {
        return $this->time->getMicro();
    }

    // -------------------------------------------------------------------------
    // MARK: comparison methods
    // -------------------------------------------------------------------------
    /**
     * Compares this date-time to another date-time.
     *
     * @param LocalDateTime $that the date-time to compare to
     *
     * @return int [-1,0,1] If this date-time is before, on, or after the given date-time
     */
    final public function compareTo(self $that): int
    {
        $cmp = $this->date->compareTo($that->date);

        if ($cmp !== 0) {
            return $cmp;
        }

        return $this->time->compareTo($that->time);
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

    /**
     * Returns whether this LocalDateTime is in the future, in the given time-zone, according to the given clock.
     */
    final public function isFuture(DateTimeZone $timeZone): bool
    {
        return $this->isAfter(self::now($timeZone));
    }

    /**
     * Returns whether this LocalDateTime is in the past, in the given time-zone, according to the given clock.
     */
    final public function isPast(DateTimeZone $timeZone): bool
    {
        return $this->isBefore(self::now($timeZone));
    }

    // -------------------------------------------------------------------------
    // MARK: arithmetic methods
    // -------------------------------------------------------------------------
    /**
     * Returns a copy of this LocalDateTime with the specified period in years added.
     */
    final public function addYears(int $years): static
    {
        if ($years === 0) {
            return $this;
        }

        return new static($this->date->addYears($years), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in months added.
     */
    final public function addMonths(int $months): static
    {
        if ($months === 0) {
            return $this;
        }

        return new static($this->date->addMonths($months), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in weeks added.
     */
    final public function addWeeks(int $weeks): static
    {
        if ($weeks === 0) {
            return $this;
        }

        return new static($this->date->addWeeks($weeks), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in days added.
     */
    final public function addDays(int $days): static
    {
        if ($days === 0) {
            return $this;
        }

        return new static($this->date->addDays($days), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in hours added.
     */
    final public function addHours(int $hours): static
    {
        if ($hours === 0) {
            return $this;
        }

        return $this->addWithOverflow($hours, 0, 0, 0, 1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in minutes added.
     */
    final public function addMinutes(int $minutes): static
    {
        if ($minutes === 0) {
            return $this;
        }

        return $this->addWithOverflow(0, $minutes, 0, 0, 1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in seconds added.
     */
    final public function addSeconds(int $seconds): static
    {
        if ($seconds === 0) {
            return $this;
        }

        return $this->addWithOverflow(0, 0, $seconds, 0, 1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in microseconds added.
     */
    final public function addMicros(int $micros): static
    {
        if ($micros === 0) {
            return $this;
        }

        return $this->addWithOverflow(0, 0, 0, $micros, 1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in years subtracted.
     */
    final public function subYears(int $years): static
    {
        if ($years === 0) {
            return $this;
        }

        return new static($this->date->subYears($years), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in months subtracted.
     */
    final public function subMonths(int $months): static
    {
        if ($months === 0) {
            return $this;
        }

        return new static($this->date->subMonths($months), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in weeks subtracted.
     */
    final public function subWeeks(int $weeks): static
    {
        if ($weeks === 0) {
            return $this;
        }

        return new static($this->date->subWeeks($weeks), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in days subtracted.
     */
    final public function subDays(int $days): static
    {
        if ($days === 0) {
            return $this;
        }

        return new static($this->date->subDays($days), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in hours subtracted.
     */
    final public function subHours(int $hours): static
    {
        if ($hours === 0) {
            return $this;
        }

        return $this->addWithOverflow($hours, 0, 0, 0, -1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in minutes subtracted.
     */
    final public function subMinutes(int $minutes): static
    {
        if ($minutes === 0) {
            return $this;
        }

        return $this->addWithOverflow(0, $minutes, 0, 0, -1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in seconds subtracted.
     */
    final public function subSeconds(int $seconds): static
    {
        if ($seconds === 0) {
            return $this;
        }

        return $this->addWithOverflow(0, 0, $seconds, 0, -1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in microseconds subtracted.
     */
    final public function subMicros(int $micros): static
    {
        if ($micros === 0) {
            return $this;
        }

        return $this->addWithOverflow(0, 0, 0, $micros, -1);
    }

    // -------------------------------------------------------------------------
    // MARK: conversion methods
    // -------------------------------------------------------------------------
    /**
     * Converts this LocalDateTime to a native DateTimeImmutable object.
     *
     * The result is a DateTimeImmutable in the UTC time-zone.
     *
     * Note that the native DateTimeImmutable object supports a precision up to the microsecond,
     */
    final public function toDateTimeImmutable(): DateTimeImmutable
    {
        return new DateTimeImmutable((string)$this, new DateTimeZone('UTC'));
    }

    // -------------------------------------------------------------------------
    // MARK: private methods
    // -------------------------------------------------------------------------

    /**
     * Returns a copy of this `LocalDateTime` with the specified period added.
     *
     * @param int $hours   The hours to add. May be negative.
     * @param int $minutes The minutes to add. May be negative.
     * @param int $seconds The seconds to add. May be negative.
     * @param int $micros  The microseconds to add. May be negative.
     * @param int $sign    the sign, validated as `1` to add or `-1` to subtract
     *
     * @return static the combined result
     */
    private function addWithOverflow(int $hours, int $minutes, int $seconds, int $micros, int $sign): static
    {
        $totDays
            = intdiv($hours, LocalTime::HOURS_PER_DAY)
            + intdiv($minutes, LocalTime::MINUTES_PER_DAY)
            + intdiv($seconds, LocalTime::SECONDS_PER_DAY);
        $totDays *= $sign;

        $totSeconds
            = ($seconds % LocalTime::SECONDS_PER_DAY)
            + ($minutes % LocalTime::MINUTES_PER_DAY) * LocalTime::SECONDS_PER_MINUTE
            + ($hours % LocalTime::HOURS_PER_DAY) * LocalTime::SECONDS_PER_HOUR;

        $curSoD = $this->time->toSecondOfDay();
        $totSeconds = $totSeconds * $sign + $curSoD;

        $totMicros = $micros * $sign + $this->time->getMicro();
        $totSeconds += Math::floorDiv($totMicros, LocalTime::MICROS_PER_SECOND);

        /** @var int<0, 999999> */
        $newMicro = Math::floorMod($totMicros, LocalTime::MICROS_PER_SECOND);

        $totDays += Math::floorDiv($totSeconds, LocalTime::SECONDS_PER_DAY);

        /** @var int<0, 86399> */
        $newSoD = Math::floorMod($totSeconds, LocalTime::SECONDS_PER_DAY);

        $newDate = $this->date->addDays($totDays);
        $newTime = ($newSoD === $curSoD ? $this->time : LocalTime::ofSecondOfDay($newSoD, $newMicro));

        return new static($newDate, $newTime);
    }
}
