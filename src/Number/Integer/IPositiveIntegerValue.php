<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

/**
 * 正の整数の値オブジェクト インターフェイス
 */
interface IPositiveIntegerValue extends IIntegerValue
{
    /**
     * ゼロを許容するかどうか
     */
    public static function isZeroAllowed(): bool;
}
