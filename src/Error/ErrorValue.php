<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Error;

use WizDevelop\PhpValueObject\IValueObject;
use WizDevelop\PhpValueObject\ValueObjectDefault;

/**
 * エラー値オブジェクト
 */
readonly class ErrorValue implements IValueObject
{
    use ValueObjectDefault;

    final public const string SEPARATOR = '||';

    protected function __construct(
        private string $code,
        private string $message,
    ) {
    }

    final public function getCode(): string
    {
        return $this->code;
    }

    final public function getMessage(): string
    {
        return $this->message;
    }

    final public function serialize(): string
    {
        return $this->code . self::SEPARATOR . $this->message;
    }

    final public static function deserialize(string $serialized): self
    {
        $exploded = explode(self::SEPARATOR, $serialized);

        assert(count($exploded) === 2);

        return new self(...$exploded);
    }
}
