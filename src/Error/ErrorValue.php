<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Error;

use Override;
use WizDevelop\PhpValueObject\IValueObject;

/**
 * エラー値オブジェクト
 */
readonly class ErrorValue implements IErrorValue
{
    /**
     * @param IErrorValue[] $details
     */
    final private function __construct(
        private string $code,
        private string $message,
        private array $details,
    ) {
    }

    /**
     * @param IErrorValue[] $details
     */
    final public static function of(string $code, string $message, array $details = []): static
    {
        return new static($code, $message, $details);
    }

    #[Override]
    final public function equals(IValueObject $other): bool
    {
        if ($this->code !== $other->getCode() || $this->message !== $other->getMessage()) {
            return false;
        }

        $otherDetails = $other->getDetails();
        if (count($this->details) !== count($otherDetails)) {
            return false;
        }

        foreach ($this->details as $i => $detail) {
            if (!$detail->equals($otherDetails[$i])) {
                return false;
            }
        }

        return true;
    }

    #[Override]
    final public function __toString(): string
    {
        return $this->serialize();
    }

    /**
     * @return array<mixed>
     */
    #[Override]
    final public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    #[Override]
    final public function getCode(): string
    {
        return $this->code;
    }

    #[Override]
    final public function getMessage(): string
    {
        return $this->message;
    }

    #[Override]
    final public function getDetails(): array
    {
        return $this->details;
    }

    #[Override]
    final public function serialize(): string
    {
        $result = $this->code . static::SEPARATOR . $this->message;

        if (count($this->details) > 0) {
            $result .= static::SEPARATOR . count($this->details);
            foreach ($this->details as $detail) {
                $result .= static::SEPARATOR . $detail->serialize();
            }
        }

        return $result;
    }

    #[Override]
    final public static function deserialize(string $serialized): static
    {
        $parts = explode(static::SEPARATOR, $serialized);
        assert(count($parts) >= 2, 'Invalid serialized error value format.');

        $code = $parts[0];
        $message = $parts[1];
        $details = [];

        if (count($parts) > 2) {
            $detailCount = (int)$parts[2];
            assert($detailCount >= 0, 'Invalid detail count in serialized error value.');

            $currentIndex = 3;
            for ($i = 0; $i < $detailCount; ++$i) {
                // 詳細エラーを再帰的にデシリアライズ
                $result = self::parseDetailAt($parts, $currentIndex);
                $details[] = $result['detail'];
                $currentIndex = $result['nextIndex'];
            }
        }

        return new static($code, $message, $details);
    }

    /**
     * 指定されたインデックスから詳細エラーをパースし、次のインデックスと共に返す
     *
     * @param  array<int, string>                         $parts      シリアライズされた文字列を分割した配列
     * @param  int                                        $startIndex パース開始インデックス
     * @return array{detail: IErrorValue, nextIndex: int}
     */
    private static function parseDetailAt(array $parts, int $startIndex): array
    {
        assert($startIndex + 1 < count($parts), 'Invalid start index for detail parsing.');

        $code = $parts[$startIndex];
        $message = $parts[$startIndex + 1];

        $currentIndex = $startIndex + 2;
        $details = [];

        // 詳細エラーが子の詳細を持つかチェック
        if ($currentIndex < count($parts) && is_numeric($parts[$currentIndex])) {
            $nestedDetailCount = (int)$parts[$currentIndex];
            ++$currentIndex; // 詳細カウント分を進める

            for ($i = 0; $i < $nestedDetailCount; ++$i) {
                $result = self::parseDetailAt($parts, $currentIndex);
                $details[] = $result['detail'];
                $currentIndex = $result['nextIndex'];
            }
        }

        return [
            'detail' => new static($code, $message, $details),
            'nextIndex' => $currentIndex,
        ];
    }
}
