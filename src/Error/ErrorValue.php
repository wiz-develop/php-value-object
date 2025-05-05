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
}
