<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

use DivisionByZeroError;
use Override;

/**
 * Default implementation of IArithmetic
 * @see WizDevelop\PhpValueObject\Number\Decimal\IArithmetic
 */
trait Arithmetic
{
    #[Override]
    public function add(IDecimalValue $other): static
    {
        return static::from($this->value()->add($other->value(), static::scale()));
    }

    #[Override]
    public function sub(IDecimalValue $other): static
    {
        return static::from($this->value()->sub($other->value(), static::scale()));
    }

    #[Override]
    public function mul(IDecimalValue $other): static
    {
        return static::from($this->value()->mul($other->value(), static::scale()));
    }

    #[Override]
    public function div(IDecimalValue $other): static
    {
        if ($other->isZero()) {
            throw new DivisionByZeroError('Division by zero');
        }

        return static::from($this->value()->div($other->value(), static::scale()));
    }
}
