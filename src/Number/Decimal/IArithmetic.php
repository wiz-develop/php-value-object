<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

/**
 * 算術演算可能な小数の値オブジェクト
 */
interface IArithmetic
{
    /**
     * 加算
     */
    public function add(IDecimalValue $other): IDecimalValue;

    /**
     * 減算
     */
    public function sub(IDecimalValue $other): IDecimalValue;

    /**
     * 乗算
     */
    public function mul(IDecimalValue $other): IDecimalValue;

    /**
     * 除算
     */
    public function div(IDecimalValue $other): IDecimalValue;
}
