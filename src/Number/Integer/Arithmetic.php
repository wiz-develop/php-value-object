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
 * @see WizDevelop\PhpValueObject\Number\Integer\IIntegerValue
 */
trait Arithmetic
{
    #[Override]
    final public function add(IIntegerValue $other): static
    {
        return static::from($this->value() + $other->value());
    }

    /**
     * @return Result<static,NumberValueError>
     */
    #[Override]
    final public function tryAdd(IIntegerValue $other): Result
    {
        return static::tryFrom($this->value() + $other->value());
    }

    #[Override]
    final public function sub(IIntegerValue $other): static
    {
        return static::from($this->value() - $other->value());
    }

    /**
     * @return Result<static,NumberValueError>
     */
    #[Override]
    final public function trySub(IIntegerValue $other): Result
    {
        return static::tryFrom($this->value() - $other->value());
    }

    #[Override]
    final public function mul(IIntegerValue $other): static
    {
        return static::from($this->value() * $other->value());
    }

    /**
     * @return Result<static,NumberValueError>
     */
    #[Override]
    final public function tryMul(IIntegerValue $other): Result
    {
        return static::tryFrom($this->value() * $other->value());
    }

    #[Override]
    final public function div(IIntegerValue $other): static
    {
        if ($other->isZero()) {
            throw new DivisionByZeroError('Division by zero');
        }

        // 整数除算
        return static::from(intdiv($this->value(), $other->value()));
    }

    /**
     * @return Result<static,NumberValueError>
     */
    #[Override]
    final public function tryDiv(IIntegerValue $other): Result
    {
        if ($other->isZero()) {
            return Result\err(NumberValueError::invalidDivideByZero(
                className: static::class,
            ));
        }

        // 整数除算
        return static::tryFrom(intdiv($this->value(), $other->value()));
    }
}
