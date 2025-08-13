<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\DateTime;

use Override;
use Stringable;
use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\IValueObject;
use WizDevelop\PhpValueObject\Utils;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * ローカル日時範囲を表す値オブジェクト
 */
#[ValueObjectMeta(name: 'ローカル日時範囲')]
readonly class LocalDateTimeRange implements IValueObject, Stringable
{
    /**
     * 最大日時（9999-12-31 23:59:59）
     */
    private const MAX_DATE_YEAR = 9999;
    private const MAX_DATE_MONTH = 12;
    private const MAX_DATE_DAY = 31;
    private const MAX_TIME_HOUR = 23;
    private const MAX_TIME_MINUTE = 59;
    private const MAX_TIME_SECOND = 59;

    /**
     * Avoid new() operator.
     */
    final private function __construct(
        private LocalDateTime $from,
        private LocalDateTime $to,
        private RangeType $rangeType
    ) {
        // NOTE: 不変条件（invariant）
        Utils::assertResultIsOk(static::isValid($from, $to));
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
     * 指定された開始日時、終了日時、範囲タイプでインスタンスを生成
     */
    final public static function from(
        LocalDateTime $from,
        ?LocalDateTime $to = null,
        RangeType $rangeType = RangeType::CLOSED
    ): static {
        $to ??= LocalDateTime::of(
            LocalDate::of(self::MAX_DATE_YEAR, self::MAX_DATE_MONTH, self::MAX_DATE_DAY),
            LocalTime::of(self::MAX_TIME_HOUR, self::MAX_TIME_MINUTE, self::MAX_TIME_SECOND)
        );

        return new static($from, $to, $rangeType);
    }

    /**
     * 閉区間でインスタンスを生成
     */
    final public static function closed(LocalDateTime $from, ?LocalDateTime $to = null): static
    {
        return static::from($from, $to, RangeType::CLOSED);
    }

    /**
     * 開区間でインスタンスを生成
     */
    final public static function open(LocalDateTime $from, ?LocalDateTime $to = null): static
    {
        return static::from($from, $to, RangeType::OPEN);
    }

    /**
     * 左開区間でインスタンスを生成
     */
    final public static function halfOpenLeft(LocalDateTime $from, ?LocalDateTime $to = null): static
    {
        return static::from($from, $to, RangeType::HALF_OPEN_LEFT);
    }

    /**
     * 右開区間でインスタンスを生成
     */
    final public static function halfOpenRight(LocalDateTime $from, ?LocalDateTime $to = null): static
    {
        return static::from($from, $to, RangeType::HALF_OPEN_RIGHT);
    }

    /**
     * @return Result<static,ValueObjectError>
     */
    final public static function tryFrom(
        LocalDateTime $from,
        ?LocalDateTime $to = null,
        RangeType $rangeType = RangeType::CLOSED
    ): Result {
        $to ??= LocalDateTime::of(
            date: LocalDate::of(self::MAX_DATE_YEAR, self::MAX_DATE_MONTH, self::MAX_DATE_DAY),
            time: LocalTime::of(self::MAX_TIME_HOUR, self::MAX_TIME_MINUTE, self::MAX_TIME_SECOND)
        );

        return static::isValid($from, $to)
            ->andThen(static fn () => Result\ok(static::from($from, $to, $rangeType)));
    }

    /**
     * @return Option<static>
     */
    final public static function fromNullable(
        ?LocalDateTime $from,
        ?LocalDateTime $to = null,
        RangeType $rangeType = RangeType::CLOSED
    ): Option {
        if ($from === null) {
            return Option\none();
        }

        return Option\some(static::from($from, $to, $rangeType));
    }

    /**
     * @return Result<Option<static>,ValueObjectError>
     */
    final public static function tryFromNullable(
        ?LocalDateTime $from,
        ?LocalDateTime $to = null,
        RangeType $rangeType = RangeType::CLOSED
    ): Result {
        if ($from === null) {
            // @phpstan-ignore return.type
            return Result\ok(Option\none());
        }

        // @phpstan-ignore return.type
        return static::tryFrom($from, $to, $rangeType)->map(static fn ($result) => Option\some($result));
    }

    // -------------------------------------------------------------------------
    // MARK: validation methods
    // -------------------------------------------------------------------------
    /**
     * 有効な値かどうか
     * @return Result<bool,ValueObjectError>
     */
    protected static function isValid(LocalDateTime $from, LocalDateTime $to): Result
    {
        if ($from->isAfter($to)) {
            return Result\err(ValueObjectError::of(
                code: 'value_object.datetime_range.invalid_range',
                message: '開始日時は終了日時以前である必要があります'
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

    final public function getFrom(): LocalDateTime
    {
        return $this->from;
    }

    final public function getTo(): LocalDateTime
    {
        return $this->to;
    }

    final public function getRangeType(): RangeType
    {
        return $this->rangeType;
    }

    /**
     * 指定された日時が範囲内に含まれるかを判定
     */
    final public function contains(LocalDateTime $dateTime): bool
    {
        $afterFrom = match ($this->rangeType) {
            RangeType::CLOSED, RangeType::HALF_OPEN_RIGHT => $dateTime->isAfterOrEqualTo($this->from),
            RangeType::OPEN, RangeType::HALF_OPEN_LEFT => $dateTime->isAfter($this->from),
        };

        $beforeTo = match ($this->rangeType) {
            RangeType::CLOSED, RangeType::HALF_OPEN_LEFT => $dateTime->isBeforeOrEqualTo($this->to),
            RangeType::OPEN, RangeType::HALF_OPEN_RIGHT => $dateTime->isBefore($this->to),
        };

        return $afterFrom && $beforeTo;
    }

    /**
     * 他の範囲と重なりがあるかを判定
     */
    final public function overlaps(self $other): bool
    {
        // 一方の範囲の終了が他方の開始より前の場合、重なりなし
        if ($this->strictlyBefore($other) || $other->strictlyBefore($this)) {
            return false;
        }

        // 境界での接触を考慮
        return $this->hasOverlapAt($other);
    }

    /**
     * この範囲が他の範囲より完全に前にあるかを判定
     */
    private function strictlyBefore(self $other): bool
    {
        return $this->to->isBefore($other->from) || (
            $this->to->equals($other->from) && (
                $this->rangeType === RangeType::OPEN
                || $this->rangeType === RangeType::HALF_OPEN_RIGHT
                || $other->rangeType === RangeType::OPEN
                || $other->rangeType === RangeType::HALF_OPEN_LEFT
            )
        );
    }

    /**
     * 境界での重なりを考慮した判定
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
     * 範囲の期間を秒単位で返す
     */
    final public function durationInSeconds(): int
    {
        $fromTimestamp = $this->from->toDateTimeImmutable()->getTimestamp();
        $toTimestamp = $this->to->toDateTimeImmutable()->getTimestamp();

        return $toTimestamp - $fromTimestamp;
    }

    /**
     * 範囲の期間を分単位で返す
     */
    final public function durationInMinutes(): float
    {
        return $this->durationInSeconds() / 60;
    }

    /**
     * 範囲の期間を時間単位で返す
     */
    final public function durationInHours(): float
    {
        return $this->durationInSeconds() / 3600;
    }

    /**
     * 範囲の期間を日単位で返す
     */
    final public function durationInDays(): float
    {
        return $this->durationInSeconds() / 86400;
    }
}
