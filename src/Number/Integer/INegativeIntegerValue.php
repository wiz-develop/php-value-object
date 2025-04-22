<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * 負の整数の値オブジェクト インターフェイス
 */
interface INegativeIntegerValue extends IIntegerValue
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
    public static function isNegative(int $value): Result;
}
