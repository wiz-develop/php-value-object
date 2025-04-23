<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * 算術演算可能な少数の値オブジェクト
 */
interface IArithmetic
{
    /**
     * 加算
     */
    public function add(DecimalValueBase $other): DecimalValueBase;

    /**
     * 加算（例外を投げない）
     * @return Result<static,NumberValueError>
     */
    public function tryAdd(DecimalValueBase $other): Result;

    /**
     * 減算
     */
    public function sub(DecimalValueBase $other): DecimalValueBase;

    /**
     * 減算（例外を投げない）
     * @return Result<static,NumberValueError>
     */
    public function trySub(DecimalValueBase $other): Result;

    /**
     * 乗算
     */
    public function mul(DecimalValueBase $other): DecimalValueBase;

    /**
     * 乗算（例外を投げない）
     * @return Result<static,NumberValueError>
     */
    public function tryMul(DecimalValueBase $other): Result;

    /**
     * 除算
     */
    public function div(DecimalValueBase $other): DecimalValueBase;

    /**
     * 除算（例外を投げない）
     * @return Result<static,NumberValueError>
     */
    public function tryDiv(DecimalValueBase $other): Result;
}
