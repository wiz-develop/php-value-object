<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

use BcMath\Number;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * 負の少数の値オブジェクト インターフェイス
 */
interface INegativeDecimalValue
{
    /**
     * ゼロを含むかどうか
     */
    public static function includeZero(): bool;

    /**
     * 負の数かどうか
     * `includeZero()` がtrueの場合は0も許容する
     * @return Result<bool,NumberValueError>
     */
    public static function isNegative(Number $value): Result;
}
