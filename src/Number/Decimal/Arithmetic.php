<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

use DivisionByZeroError;
use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\NumberValueError;

/**
 * Default implementation of IArithmetic
 * @see WizDevelop\PhpValueObject\Number\Decimal\IArithmetic
 * @see WizDevelop\PhpValueObject\Number\Decimal\DecimalValueBase
 * @see WizDevelop\PhpValueObject\Number\Decimal\IDecimalValueFactory
 */
trait Arithmetic
{
    #[Override]
    final public function add(DecimalValueBase $other): static
    {
        return static::from($this->value->add($other->value, static::scale()));
    }

    /**
     * @return Result<static,NumberValueError>
     */
    #[Override]
    final public function tryAdd(DecimalValueBase $other): Result
    {
        return static::tryFrom($this->value->add($other->value, static::scale()));
    }

    #[Override]
    final public function sub(DecimalValueBase $other): static
    {
        return static::from($this->value->sub($other->value, static::scale()));
    }

    /**
     * @return Result<static,NumberValueError>
     */
    #[Override]
    final public function trySub(DecimalValueBase $other): Result
    {
        return static::tryFrom($this->value->sub($other->value, static::scale()));
    }

    #[Override]
    final public function mul(DecimalValueBase $other): static
    {
        return static::from($this->value->mul($other->value, static::scale()));
    }

    /**
     * @return Result<static,NumberValueError>
     */
    #[Override]
    final public function tryMul(DecimalValueBase $other): Result
    {
        return static::tryFrom($this->value->mul($other->value, static::scale()));
    }

    #[Override]
    final public function div(DecimalValueBase $other): static
    {
        if ($other->isZero()) {
            throw new DivisionByZeroError('Division by zero');
        }

        return static::from($this->value->div($other->value, static::scale()));
    }

    /**
     * @return Result<static,NumberValueError>
     */
    #[Override]
    final public function tryDiv(DecimalValueBase $other): Result
    {
        if ($other->isZero()) {
            return Result\err(NumberValueError::invalidDivideByZero(
                className: static::class,
            ));
        }

        return static::tryFrom($this->value->div($other->value, static::scale()));
    }
}
