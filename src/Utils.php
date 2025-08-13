<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject;

use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;

/**
 * @internal
 */
final readonly class Utils
{
    /**
     * @param Result<bool,ValueObjectError> $result
     */
    public static function assertResultIsOk(Result $result): void
    {
        assert($result->isOk(), $result->mapOrElse(
            static fn () => 'Ok',
            static fn ($error) => "error_code: {$error->getCode()}, message: {$error->getMessage()}, details: " . json_encode($error->getDetails(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        ));
    }
}
