<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Boolean;

use WizDevelop\PhpValueObject\Boolean\Base\BooleanValueBase;
use WizDevelop\PhpValueObject\Error\ValueObjectError;

/**
 * BooleanValue エラー
 * @extends ValueObjectError<BooleanValueBase>
 */
final readonly class BooleanValueError extends ValueObjectError
{
    public static function invalid(
        string $message,
    ): static {
        return new self(
            code: __METHOD__,
            message: $message,
        );
    }
}
