<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

/**
 * 比較可能な小数の値オブジェクト
 */
interface IComparison
{
    /**
     * 大きいか
     */
    public function gt(IDecimalValue $other): bool;

    /**
     * 大きいか同じ
     */
    public function gte(IDecimalValue $other): bool;

    /**
     * 小さいか
     */
    public function lt(IDecimalValue $other): bool;

    /**
     * 小さいか同じ
     */
    public function lte(IDecimalValue $other): bool;
}
