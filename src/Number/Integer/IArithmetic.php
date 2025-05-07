<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;

/**
 * 算術演算可能な整数の値オブジェクト
 */
interface IArithmetic
{
    /**
     * 加算
     */
    public function add(IntegerValueBase $other): static;

    /**
     * 加算（例外を投げない）
     * @return Result<static,ValueObjectError>
     */
    public function tryAdd(IntegerValueBase $other): Result;

    /**
     * 減算
     */
    public function sub(IntegerValueBase $other): static;

    /**
     * 減算（例外を投げない）
     * @return Result<static,ValueObjectError>
     */
    public function trySub(IntegerValueBase $other): Result;

    /**
     * 乗算
     */
    public function mul(IntegerValueBase $other): static;

    /**
     * 乗算（例外を投げない）
     * @return Result<static,ValueObjectError>
     */
    public function tryMul(IntegerValueBase $other): Result;

    /**
     * 除算
     */
    public function div(IntegerValueBase $other): static;

    /**
     * 除算（例外を投げない）
     * @return Result<static,ValueObjectError>
     */
    public function tryDiv(IntegerValueBase $other): Result;
}
