<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

/**
 * 負の整数の値オブジェクト インターフェイス
 */
interface INegativeIntegerValue extends IIntegerValue
{
    /**
     * ゼロを許容するかどうか
     */
    public static function isZeroAllowed(): bool;
}
