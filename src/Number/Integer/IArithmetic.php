<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * 算術演算可能な整数の値オブジェクト
 */
interface IArithmetic
{
    /**
     * 加算
     */
    public function add(IIntegerValue $other): IIntegerValue;

    /**
     * 加算（例外を投げない）
     * @return Result<static,NumberValueError>
     */
    public function tryAdd(IIntegerValue $other): Result;

    /**
     * 減算
     */
    public function sub(IIntegerValue $other): IIntegerValue;

    /**
     * 減算（例外を投げない）
     * @return Result<static,NumberValueError>
     */
    public function trySub(IIntegerValue $other): Result;

    /**
     * 乗算
     */
    public function mul(IIntegerValue $other): IIntegerValue;

    /**
     * 乗算（例外を投げない）
     * @return Result<static,NumberValueError>
     */
    public function tryMul(IIntegerValue $other): Result;

    /**
     * 除算
     */
    public function div(IIntegerValue $other): IIntegerValue;

    /**
     * 除算（例外を投げない）
     * @return Result<static,NumberValueError>
     */
    public function tryDiv(IIntegerValue $other): Result;
}
