<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number;

use Generator;
use JsonSerializable;
use Override;
use Stringable;
use WizDevelop\PhpMonad\Option;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\DateTime\RangeType;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\IValueObject;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * 整数の範囲を表す値オブジェクト
 */
#[ValueObjectMeta(name: '整数範囲')]
final readonly class IntegerRange implements IValueObject, Stringable, JsonSerializable
{
    /**
     * @param int       $from      開始値
     * @param int       $to        終了値
     * @param RangeType $rangeType 範囲タイプ
     */
    private function __construct(
        private int $from,
        private int $to,
        private RangeType $rangeType
    ) {
        // NOTE: 不変条件（invariant）
        assert(self::isValid($from, $to)->isOk());
    }

    /**
     * 指定された整数値から範囲を生成する
     *
     * @param int       $from      開始値
     * @param int       $to        終了値
     * @param RangeType $rangeType 範囲タイプ（デフォルト：HALF_OPEN_RIGHT）
     */
    public static function from(int $from, int $to, RangeType $rangeType = RangeType::HALF_OPEN_RIGHT): self
    {
        return new self($from, $to, $rangeType);
    }

    /**
     * 閉区間 [from, to] を生成する
     *
     * @param int $from 開始値
     * @param int $to   終了値
     */
    public static function closed(int $from, int $to): self
    {
        return new self($from, $to, RangeType::CLOSED);
    }

    /**
     * 開区間 (from, to) を生成する
     *
     * @param int $from 開始値
     * @param int $to   終了値
     */
    public static function open(int $from, int $to): self
    {
        return new self($from, $to, RangeType::OPEN);
    }

    /**
     * 左開区間 (from, to] を生成する
     *
     * @param int $from 開始値
     * @param int $to   終了値
     */
    public static function halfOpenLeft(int $from, int $to): self
    {
        return new self($from, $to, RangeType::HALF_OPEN_LEFT);
    }

    /**
     * 右開区間 [from, to) を生成する
     *
     * @param int $from 開始値
     * @param int $to   終了値
     */
    public static function halfOpenRight(int $from, int $to): self
    {
        return new self($from, $to, RangeType::HALF_OPEN_RIGHT);
    }

    /**
     * 指定された整数値から範囲を生成する（エラーハンドリング付き）
     *
     * @param  int                            $from      開始値
     * @param  int                            $to        終了値
     * @param  RangeType                      $rangeType 範囲タイプ（デフォルト：HALF_OPEN_RIGHT）
     * @return Result<self, ValueObjectError>
     */
    public static function tryFrom(int $from, int $to, RangeType $rangeType = RangeType::HALF_OPEN_RIGHT): Result
    {
        $validationResult = self::isValid($from, $to);
        if ($validationResult->isErr()) {
            return $validationResult;
        }

        return Result\ok(new self($from, $to, $rangeType));
    }

    /**
     * 指定された整数値から範囲を生成する（null許容）
     *
     * @param  int|null     $from      開始値
     * @param  int|null     $to        終了値
     * @param  RangeType    $rangeType 範囲タイプ（デフォルト：HALF_OPEN_RIGHT）
     * @return Option<self>
     */
    public static function fromNullable(?int $from, ?int $to, RangeType $rangeType = RangeType::HALF_OPEN_RIGHT): Option
    {
        if ($from === null || $to === null) {
            return Option\none();
        }

        return Option\some(new self($from, $to, $rangeType));
    }

    /**
     * 指定された整数値から範囲を生成する（null許容、エラーハンドリング付き）
     *
     * @param  int|null                               $from      開始値
     * @param  int|null                               $to        終了値
     * @param  RangeType                              $rangeType 範囲タイプ（デフォルト：HALF_OPEN_RIGHT）
     * @return Result<Option<self>, ValueObjectError>
     */
    public static function tryFromNullable(?int $from, ?int $to, RangeType $rangeType = RangeType::HALF_OPEN_RIGHT): Result
    {
        if ($from === null || $to === null) {
            // @phpstan-ignore-next-line
            return Result\ok(Option\none());
        }

        // @phpstan-ignore-next-line
        return self::tryFrom($from, $to, $rangeType)->map(
            static fn (self $range) => Option\some($range)
        );
    }

    /**
     * 開始値と終了値の妥当性を検証する
     *
     * @param  int                            $from 開始値
     * @param  int                            $to   終了値
     * @return Result<bool, ValueObjectError>
     */
    private static function isValid(int $from, int $to): Result
    {
        if ($from > $to) {
            return Result\err(ValueObjectError::of(
                code: 'value_object.integer_range.invalid_range',
                message: '開始値は終了値以下である必要があります'
            ));
        }

        return Result\ok(true);
    }

    /**
     * 開始値を取得する
     */
    public function getFrom(): int
    {
        return $this->from;
    }

    /**
     * 終了値を取得する
     */
    public function getTo(): int
    {
        return $this->to;
    }

    /**
     * 範囲タイプを取得する
     */
    public function getRangeType(): RangeType
    {
        return $this->rangeType;
    }

    /**
     * 指定された整数が範囲内に含まれるかを判定する
     *
     * @param int $value 判定する整数
     */
    public function contains(int $value): bool
    {
        return match ($this->rangeType) {
            RangeType::CLOSED => $this->from <= $value && $value <= $this->to,
            RangeType::OPEN => $this->from < $value && $value < $this->to,
            RangeType::HALF_OPEN_LEFT => $this->from < $value && $value <= $this->to,
            RangeType::HALF_OPEN_RIGHT => $this->from <= $value && $value < $this->to,
        };
    }

    /**
     * 他の範囲と重なりがあるかを判定する
     *
     * @param IntegerRange $other 比較対象の範囲
     */
    public function overlaps(self $other): bool
    {
        // 明らかに重ならない場合
        if ($this->strictlyBefore($other) || $other->strictlyBefore($this)) {
            return false;
        }

        // 境界での重なりを判定
        return $this->hasOverlapAt($other);
    }

    /**
     * この範囲が他の範囲よりも完全に前にあるかを判定する
     *
     * @param IntegerRange $other 比較対象の範囲
     */
    private function strictlyBefore(self $other): bool
    {
        return $this->to < $other->from || ($this->to === $other->from && !$this->hasOverlapAt($other));
    }

    /**
     * 境界での重なりがあるかを判定する
     *
     * @param IntegerRange $other 比較対象の範囲
     */
    private function hasOverlapAt(self $other): bool
    {
        // 共通の値が存在するかチェック
        $maxFrom = max($this->from, $other->from);
        $minTo = min($this->to, $other->to);

        if ($maxFrom > $minTo) {
            return false;
        }

        // 境界値での包含を確認
        for ($value = $maxFrom; $value <= $minTo; ++$value) {
            if ($this->contains($value) && $other->contains($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 範囲内の整数の個数を取得する
     */
    public function count(): int
    {
        if ($this->from > $this->to) {
            return 0;
        }

        $baseCount = $this->to - $this->from + 1;

        return match ($this->rangeType) {
            RangeType::CLOSED => $baseCount,
            RangeType::OPEN => max(0, $baseCount - 2),
            RangeType::HALF_OPEN_LEFT => max(0, $baseCount - 1),
            RangeType::HALF_OPEN_RIGHT => max(0, $baseCount - 1),
        };
    }

    /**
     * 範囲内の整数を順に返すジェネレータを取得する
     *
     * @return Generator<int>
     */
    public function iterate(): Generator
    {
        $start = match ($this->rangeType) {
            RangeType::CLOSED, RangeType::HALF_OPEN_RIGHT => $this->from,
            RangeType::OPEN, RangeType::HALF_OPEN_LEFT => $this->from + 1,
        };

        $end = match ($this->rangeType) {
            RangeType::CLOSED, RangeType::HALF_OPEN_LEFT => $this->to,
            RangeType::OPEN, RangeType::HALF_OPEN_RIGHT => $this->to - 1,
        };

        for ($i = $start; $i <= $end; ++$i) {
            yield $i;
        }
    }

    /**
     * 文字列表現を取得する
     */
    #[Override]
    public function __toString(): string
    {
        $leftBracket = match ($this->rangeType) {
            RangeType::CLOSED, RangeType::HALF_OPEN_RIGHT => '[',
            RangeType::OPEN, RangeType::HALF_OPEN_LEFT => '(',
        };

        $rightBracket = match ($this->rangeType) {
            RangeType::CLOSED, RangeType::HALF_OPEN_LEFT => ']',
            RangeType::OPEN, RangeType::HALF_OPEN_RIGHT => ')',
        };

        return "{$leftBracket}{$this->from}, {$this->to}{$rightBracket}";
    }

    /**
     * JSON表現用のデータを取得する
     *
     * @return array{from: int, to: int, rangeType: string}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
            'rangeType' => $this->rangeType->value,
        ];
    }

    /**
     * 他の値オブジェクトと等価かを判定する
     *
     * @param IValueObject $other 比較対象
     */
    public function equals(IValueObject $other): bool
    {
        return $other instanceof self
            && $this->from === $other->from
            && $this->to === $other->to
            && $this->rangeType === $other->rangeType;
    }
}
