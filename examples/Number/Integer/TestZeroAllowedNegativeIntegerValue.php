<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Number\Integer;

use Override;
use WizDevelop\PhpValueObject\Number\Integer\NegativeIntegerValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * ゼロを許容する負の整数の値オブジェクト
 */
#[ValueObjectMeta(displayName: '非正整数')]
final readonly class TestZeroAllowedNegativeIntegerValue extends NegativeIntegerValue
{
    /**
     * 最小値は-1000
     */
    #[Override]
    public static function min(): int
    {
        return -1000;
    }

    /**
     * ゼロを許容する
     */
    #[Override]
    public static function includeZero(): bool
    {
        return true;
    }
}
