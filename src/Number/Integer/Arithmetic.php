<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

use DivisionByZeroError;
use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * Default implementation of IArithmetic
 * @see WizDevelop\PhpValueObject\Number\Integer\IArithmetic
 * @see WizDevelop\PhpValueObject\Number\Integer\IntegerValueBase
 * @see WizDevelop\PhpValueObject\Number\Integer\IIntegerValueFactory
 */
trait Arithmetic
{
    #[Override]
    final public function add(IntegerValueBase $other): static
    {
        return static::from($this->value + $other->value);
    }

    /**
     * @return Result<static,NumberValueError>
     */
    #[Override]
    final public function tryAdd(IntegerValueBase $other): Result
    {
        return static::tryFrom($this->value + $other->value);
    }

    #[Override]
    final public function sub(IntegerValueBase $other): static
    {
        return static::from($this->value - $other->value);
    }

    /**
     * @return Result<static,NumberValueError>
     */
    #[Override]
    final public function trySub(IntegerValueBase $other): Result
    {
        return static::tryFrom($this->value - $other->value);
    }

    #[Override]
    final public function mul(IntegerValueBase $other): static
    {
        return static::from($this->value * $other->value);
    }

    /**
     * @return Result<static,NumberValueError>
     */
    #[Override]
    final public function tryMul(IntegerValueBase $other): Result
    {
        return static::tryFrom($this->value * $other->value);
    }

    #[Override]
    final public function div(IntegerValueBase $other): static
    {
        if ($other->isZero()) {
            throw new DivisionByZeroError('Division by zero');
        }

        // 整数除算
        return static::from(intdiv($this->value, $other->value));
    }

    /**
     * @return Result<static,NumberValueError>
     */
    #[Override]
    final public function tryDiv(IntegerValueBase $other): Result
    {
        if ($other->isZero()) {
            return Result\err(NumberValueError::invalidDivideByZero(
                className: static::class,
            ));
        }

        return static::tryFrom(intdiv($this->value, $other->value));
    }
}
