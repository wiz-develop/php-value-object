<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\DateTime;

use Countable;
use Generator;
use IteratorAggregate;
use Override;
use Stringable;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\IValueObject;

/**
 * @template TStart of LocalDate
 * @template TEnd of LocalDate
 *
 * @implements IteratorAggregate<LocalDate>
 *
 * ローカル日付範囲を表す値オブジェクト
 */
readonly class LocalDateRange implements IValueObject, Stringable, IteratorAggregate, Countable
{
    /**
     * Avoid new() operator.
     *
     * @param TStart $from 開始日付
     * @param TEnd   $to   終了日付
     */
    final private function __construct(
        private mixed $from,
        private mixed $to,
        private RangeType $rangeType
    ) {
        // NOTE: 不変条件（invariant）
        assert(static::isValid($from, $to)->isOk());
    }

    // -------------------------------------------------------------------------
    // MARK: implement IValueObject
    // -------------------------------------------------------------------------
    #[Override]
    final public function equals(IValueObject $other): bool
    {
        return $this->from->equals($other->from)
            && $this->to->equals($other->to)
            && $this->rangeType === $other->rangeType;
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
     * 指定された開始日付、終了日付、範囲タイプでインスタンスを生成
     *
     * @param TStart $from 開始日付
     * @param TEnd   $to   終了日付
     *
     * @return static<TStart,TEnd>
     */
    final public static function from(
        mixed $from,
        mixed $to,
        RangeType $rangeType = RangeType::HALF_OPEN_RIGHT
    ): static {
        return new static($from, $to, $rangeType);
    }

    /**
     * @param TStart $from 開始日付
     * @param TEnd   $to   終了日付
     *
     * @return Result<static<TStart,TEnd>,ValueObjectError>
     */
    final public static function tryFrom(
        mixed $from,
        mixed $to,
        RangeType $rangeType = RangeType::HALF_OPEN_RIGHT
    ): Result {
        return static::isValid($from, $to)
            ->andThen(static fn () => Result\ok(static::from($from, $to, $rangeType)));
    }

    // -------------------------------------------------------------------------
    // MARK: validation methods
    // -------------------------------------------------------------------------
    /**
     * 有効な値かどうか
     *
     * @param TStart $from 開始日付
     * @param TEnd   $to   終了日付
     *
     * @return Result<bool,ValueObjectError>
     */
    protected static function isValid(mixed $from, mixed $to): Result
    {
        if ($from->isAfter($to)) {
            return Result\err(ValueObjectError::of(
                code: 'value_object.date_range.invalid_range',
                message: '開始日付は終了日付以前である必要があります'
            ));
        }

        return Result\ok(true);
    }

    // -------------------------------------------------------------------------
    // MARK: public methods
    // -------------------------------------------------------------------------
    /**
     * ISO 8601形式の文字列表現を返す
     */
    final public function toISOString(): string
    {
        $leftBracket = match ($this->rangeType) {
            RangeType::CLOSED, RangeType::HALF_OPEN_RIGHT => '[',
            RangeType::OPEN, RangeType::HALF_OPEN_LEFT => '(',
        };

        $rightBracket = match ($this->rangeType) {
            RangeType::CLOSED, RangeType::HALF_OPEN_LEFT => ']',
            RangeType::OPEN, RangeType::HALF_OPEN_RIGHT => ')',
        };

        return sprintf('%s%s, %s%s', $leftBracket, $this->from->toISOString(), $this->to->toISOString(), $rightBracket);
    }

    final public function getFrom(): LocalDate
    {
        return $this->from;
    }

    final public function getTo(): LocalDate
    {
        return $this->to;
    }

    final public function getRangeType(): RangeType
    {
        return $this->rangeType;
    }

    /**
     * @param TStart $from 開始日付
     *
     * @return static<TStart,TEnd>
     */
    final public function withFrom(mixed $from): static
    {
        return static::from($from, $this->to, $this->rangeType);
    }

    /**
     * @param TEnd $to 終了日付
     *
     * @return static<TStart,TEnd>
     */
    final public function withTo(mixed $to): static
    {
        return static::from($this->from, $to, $this->rangeType);
    }

    /**
     * @param TStart $from 開始日付
     *
     * @return Result<static<TStart,TEnd>,ValueObjectError>
     */
    final public function tryWithFrom(mixed $from): Result
    {
        return static::tryFrom($from, $this->to, $this->rangeType);
    }

    /**
     * @param TEnd $to 終了日付
     *
     * @return Result<static<TStart,TEnd>,ValueObjectError>
     */
    final public function tryWithTo(mixed $to): Result
    {
        return static::tryFrom($this->from, $to, $this->rangeType);
    }

    /**
     * 指定された日付が範囲内に含まれるかを判定
     */
    final public function contains(LocalDate $date): bool
    {
        $afterFrom = match ($this->rangeType) {
            RangeType::CLOSED, RangeType::HALF_OPEN_RIGHT => $date->isAfterOrEqualTo($this->from),
            RangeType::OPEN, RangeType::HALF_OPEN_LEFT => $date->isAfter($this->from),
        };

        $beforeTo = match ($this->rangeType) {
            RangeType::CLOSED, RangeType::HALF_OPEN_LEFT => $date->isBeforeOrEqualTo($this->to),
            RangeType::OPEN, RangeType::HALF_OPEN_RIGHT => $date->isBefore($this->to),
        };

        return $afterFrom && $beforeTo;
    }

    /**
     * 他の範囲と重なりがあるかを判定
     *
     * @param self<TStart,TEnd> $other
     */
    final public function overlaps(self $other): bool
    {
        // 一方の範囲の終了が他方の開始より前の場合、重なりなし
        return !($this->strictlyBefore($other) || $other->strictlyBefore($this));
        // 境界での接触を考慮
        // return $this->hasOverlapAt($other);
    }

    /**
     * この範囲が他の範囲より完全に前にあるかを判定
     *
     * @param self<TStart,TEnd> $other
     */
    final public function strictlyBefore(self $other): bool
    {
        return $this->to->isBefore($other->from) || (
            $this->to->is($other->from) && (
                $this->rangeType === RangeType::OPEN
                || $this->rangeType === RangeType::HALF_OPEN_RIGHT
                || $other->rangeType === RangeType::OPEN
                || $other->rangeType === RangeType::HALF_OPEN_LEFT
            )
        );
    }

    /**
     * 境界での重なりを考慮した判定
     *
     * @param self<TStart,TEnd> $other
     */
    private function hasOverlapAt(self $other): bool
    {
        // 開始点での重なり判定
        $startOverlap = $this->contains($other->from) || $other->contains($this->from);

        // 終了点での重なり判定
        $endOverlap = $this->contains($other->to) || $other->contains($this->to);

        // 一方が他方を完全に含む場合
        $containment = ($this->from->isBeforeOrEqualTo($other->from) && $this->to->isAfterOrEqualTo($other->to))
            || ($other->from->isBeforeOrEqualTo($this->from) && $other->to->isAfterOrEqualTo($this->to));

        return $startOverlap || $endOverlap || $containment;
    }

    /**
     * 範囲の日数を返す
     * 注意: 開区間の場合、実際の日数は計算結果より1日または2日少なくなる可能性があります
     */
    final public function days(): int
    {
        $fromTimestamp = $this->from->toDateTimeImmutable()->getTimestamp();
        $toTimestamp = $this->to->toDateTimeImmutable()->getTimestamp();

        $days = (int)(($toTimestamp - $fromTimestamp) / 86400);

        // 区間タイプによる調整
        return match ($this->rangeType) {
            RangeType::CLOSED => $days + 1,  // 両端を含む
            RangeType::OPEN => max(0, $days - 1),  // 両端を含まない
            RangeType::HALF_OPEN_LEFT, RangeType::HALF_OPEN_RIGHT => $days,  // 片方の端を含む
        };
    }

    /**
     * 範囲に含まれる各日付を順に返すイテレータを取得
     * @return Generator<LocalDate>
     */
    #[Override]
    final public function getIterator(): Generator
    {
        $current = match ($this->rangeType) {
            RangeType::CLOSED, RangeType::HALF_OPEN_RIGHT => $this->from,
            RangeType::OPEN, RangeType::HALF_OPEN_LEFT => $this->from->addDays(1),
        };

        $endCondition = match ($this->rangeType) {
            RangeType::CLOSED, RangeType::HALF_OPEN_LEFT => fn (LocalDate $date) => $date->isBeforeOrEqualTo($this->to),
            RangeType::OPEN, RangeType::HALF_OPEN_RIGHT => fn (LocalDate $date) => $date->isBefore($this->to),
        };

        while ($endCondition($current)) {
            yield $current;
            $current = $current->addDays(1);
        }
    }

    /**
     * Returns the number of days in this range.
     */
    #[Override]
    final public function count(): int
    {
        $count = $this->to->toEpochDay() - $this->from->toEpochDay() + 1;

        assert($count >= 0, 'Count must be non-negative');

        return $count;
    }
}
