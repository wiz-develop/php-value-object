<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Error;

/**
 * General エラー
 */
final readonly class GeneralError
{
    public static function invalid(string $message): ValueObjectError
    {
        return ValueObjectError::of(
            code: 'value_object.general.invalid',
            message: $message,
        );
    }
}
