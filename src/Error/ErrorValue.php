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
    final private function __construct(
        private string $code,
        private string $message,
    ) {
    }

    public static function of(string $code, string $message): static
    {
        return new static($code, $message);
    }

    #[Override]
    final public function equals(IValueObject $other): bool
    {
        return $this->code === $other->code;
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
    final public function serialize(): string
    {
        return $this->code . static::SEPARATOR . $this->message;
    }

    #[Override]
    final public static function deserialize(string $serialized): static
    {
        $exploded = explode(static::SEPARATOR, $serialized);

        assert(count($exploded) === 2);

        return new static(...$exploded);
    }
}
