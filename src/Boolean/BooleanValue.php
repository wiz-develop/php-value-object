<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Boolean;

use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Boolean\Base\BooleanValueBase;
use WizDevelop\PhpValueObject\Boolean\Base\BooleanValueFactory;

/**
 * 真偽値の値オブジェクトの性質を提供する
 */
readonly class BooleanValue extends BooleanValueBase
{
    use BooleanValueFactory;

    /**
     * Avoid new() operator.
     */
    final private function __construct(bool $value)
    {
        parent::__construct($value);
    }

    #[Override]
    final public static function tryFrom(bool $value): Result
    {
        return static::isValid($value)
            ->andThen(static fn () => Result\ok(static::from($value)));
    }
}
