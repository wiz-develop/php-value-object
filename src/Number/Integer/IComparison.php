<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

/**
 * 比較可能な整数の値オブジェクト
 */
interface IComparison
{
    /**
     * 大きいか
     */
    public function gt(IntegerValueBase $other): bool;

    /**
     * 大きいか同じ
     */
    public function gte(IntegerValueBase $other): bool;

    /**
     * 小さいか
     */
    public function lt(IntegerValueBase $other): bool;

    /**
     * 小さいか同じ
     */
    public function lte(IntegerValueBase $other): bool;
}
