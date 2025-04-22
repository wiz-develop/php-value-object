<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Number\Integer;

use Override;
use WizDevelop\PhpValueObject\Number\Integer\PositiveIntegerValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * ゼロを許容する正の整数の値オブジェクト
 */
#[ValueObjectMeta(displayName: '非負整数')]
final readonly class TestZeroAllowedPositiveIntegerValue extends PositiveIntegerValue
{
    /**
     * 最大値は1000
     */
    #[Override]
    public static function max(): int
    {
        return 1000;
    }

    /**
     * ゼロを許容する
     */
    #[Override]
    public static function isZeroAllowed(): bool
    {
        return true;
    }
}
