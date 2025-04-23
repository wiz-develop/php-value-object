<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

/**
 * 比較可能な少数の値オブジェクト
 */
interface IComparison
{
    /**
     * 大きいか
     */
    public function gt(DecimalValueBase $other): bool;

    /**
     * 大きいか同じ
     */
    public function gte(DecimalValueBase $other): bool;

    /**
     * 小さいか
     */
    public function lt(DecimalValueBase $other): bool;

    /**
     * 小さいか同じ
     */
    public function lte(DecimalValueBase $other): bool;
}
