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
    public function add(IntegerValueBase $other): IntegerValueBase;

    /**
     * 加算（例外を投げない）
     * @return Result<static,NumberValueError>
     */
    public function tryAdd(IntegerValueBase $other): Result;

    /**
     * 減算
     */
    public function sub(IntegerValueBase $other): IntegerValueBase;

    /**
     * 減算（例外を投げない）
     * @return Result<static,NumberValueError>
     */
    public function trySub(IntegerValueBase $other): Result;

    /**
     * 乗算
     */
    public function mul(IntegerValueBase $other): IntegerValueBase;

    /**
     * 乗算（例外を投げない）
     * @return Result<static,NumberValueError>
     */
    public function tryMul(IntegerValueBase $other): Result;

    /**
     * 除算
     */
    public function div(IntegerValueBase $other): IntegerValueBase;

    /**
     * 除算（例外を投げない）
     * @return Result<static,NumberValueError>
     */
    public function tryDiv(IntegerValueBase $other): Result;
}
